<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

trait LoggerTrait
{
    protected static $logName = null;
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;
    protected static $logAttributes = ['*'];
    protected static $ignoreAttributes = ['created_at', 'updated_at', 'deleted_at'];
    protected static $logAttributeFormat = null;
    protected static $batchUuid = null;

    protected static function bootLoggerTrait(): void
    {
        static::created(function (Model $model) {
            $model->logActivity('created');
        });

        static::updated(function (Model $model) {
            $model->logActivity('updated');
        });

        static::deleted(function (Model $model) {
            $model->logActivity('deleted');
        });

        if (method_exists(static::class, 'restored')) {
            static::restored(function (Model $model) {
                $model->logActivity('restored');
            });
        }
    }

    public function getLogName(): string
    {
        return static::$logName ?? Str::snake(class_basename($this));
    }

    protected function getLogDescription(string $event): string
    {
        return ucfirst($event) . ' ' . Str::snake(class_basename($this), ' ');
    }

    protected function getLogProperties(string $event): array
    {
        $properties = [];
        $attributes = $this->prepareAttributes($this->getAttributes());

        if ($event === 'created') {
            $properties['attributes'] = $this->formatAttributesForLog($attributes);
            $properties['changes_summary'] = $this->buildSummary(array_fill_keys(array_keys($attributes), null), $attributes);
        } elseif ($event === 'updated') {
            $oldValues = $this->prepareAttributes($this->getOriginal());
            $properties['old'] = $this->formatAttributesForLog($oldValues);
            $properties['attributes'] = $this->formatAttributesForLog($attributes);
            $properties['changes_summary'] = $this->buildSummary($oldValues, $attributes);
        } elseif ($event === 'deleted') {
            $properties['attributes'] = $this->formatAttributesForLog($attributes);
            $properties['changes_summary'] = $this->buildSummary($attributes, array_fill_keys(array_keys($attributes), null));
        } elseif ($event === 'restored') {
            $properties['attributes'] = $this->formatAttributesForLog($attributes);
            $lastDeletedState = $this->activities()
                ->where('event', 'deleted')
                ->latest()
                ->first()?->properties['attributes'] ?? [];
            $properties['changes_summary'] = $this->buildSummary($lastDeletedState, $attributes);
        }

        return $properties;
    }

    protected function prepareAttributes(array $attributes): array
    {
        if (static::$logAttributes === ['*']) {
            $attributes = array_diff_key($attributes, array_flip(static::$ignoreAttributes));
        } else {
            $attributes = array_intersect_key($attributes, array_flip(static::$logAttributes));
        }

        if (static::$logAttributeFormat) {
            $attributes = array_map(static::$logAttributeFormat, $attributes);
        }

        return $attributes;
    }

    protected function formatAttributesForLog(array $attributes): array
    {
        return array_map(fn ($value) => $this->formatValueForComparison($value), $attributes);
    }

    protected function buildSummary(array $oldValues, array $newValues): array
    {
        $keys = array_unique(array_merge(array_keys($oldValues ?? []), array_keys($newValues ?? [])));
        $summary = [];

        foreach ($keys as $key) {
            $summary[] = [
                'field' => $key,
                'from' => $this->formatValueForComparison($oldValues[$key] ?? null),
                'to' => $this->formatValueForComparison($newValues[$key] ?? null),
            ];
        }

        return $summary;
    }

    protected function formatValueForComparison($value)
    {
        if (is_bool($value) || is_null($value)) {
            return $value;
        }

        if ($value instanceof DateTime || $value instanceof Carbon) {
            return $value->format('Y-m-d H:i:s');
        }

        if (is_array($value) || (is_object($value) && method_exists($value, 'toArray'))) {
            return json_encode(is_array($value) ? $value : $value->toArray());
        }

        if (is_object($value)) {
            return method_exists($value, '__toString')
                ? (string) $value
                : '[Object:' . get_class($value) . ']';
        }

        try {
            return (string) $value;
        } catch (Exception $e) {
            return '[Unconvertible Value]';
        }
    }

    protected function getLocationFromIp(?string $ip): array
    {
        if (!$ip) {
            return $this->fallbackLocation();
        }

        try {
            $response = @file_get_contents("https://api.2ip.io/geo.json?api=kapix2g4zn9piiks&ip={$ip}");
            if ($response) {
                $data = json_decode($response);
                if ($data && isset($data->city)) {
                    return [
                        'city' => $data->city ?? 'Unknown',
                        'region' => $data->region ?? '',
                        'country' => $data->country ?? '',
                        'address' => trim("{$data->city}, {$data->region}, {$data->country}", ', '),
                        'latitude' => $data->latitude ?? null,
                        'longitude' => $data->longitude ?? null,
                    ];
                }
            }
        } catch (Exception $e) {
            Log::error('2IP.io API Error: ' . $e->getMessage());
        }

        return $this->fallbackLocation();
    }

    protected function fallbackLocation(): array
    {
        return [
            'city' => gethostname(),
            'region' => php_uname('n'),
            'country' => 'Local Network',
            'address' => gethostname(),
            'latitude' => null,
            'longitude' => null,
        ];
    }

    public function logActivity(string $event, array $properties = []): ?ActivityLog
    {
        if (!static::$submitEmptyLogs && $event === 'updated' && empty($this->getDirty())) {
            return null;
        }

        $logProperties = array_merge($this->getLogProperties($event), $properties);
        $ip = request()->ip();
        $location = $this->getLocationFromIp($ip);

        $log = ActivityLog::create([
            'log_name' => $this->getLogName(),
            'description' => $this->getLogDescription($event),
            'subject_type' => get_class($this),
            'subject_id' => $this->getKey(),
            'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
            'causer_id' => Auth::id(),
            'properties' => $logProperties,
            'old_values' => $logProperties['old'] ?? null,
            'new_values' => $logProperties['attributes'] ?? null,
            'event' => $event,
            'batch_uuid' => static::$batchUuid,
            'ip_address' => $ip,
            'mac_address' => $this->resolveMacAddress(),
            'user_agent' => Request::userAgent(),
            'address' => $location['address'],
            'latitude' => $location['latitude'],
            'longitude' => $location['longitude'],
        ]);

        $eventClass = 'App\\Events\\ActivityLogCreated';
        if (class_exists($eventClass) && method_exists($eventClass, 'dispatch')) {
            $eventClass::dispatch($log);
        }

        return $log;
    }

    protected function resolveMacAddress(): ?string
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return exec("getmac | findstr /V 'Disconnected'") ?: null;
        }

        return exec("cat /sys/class/net/$(ip route show default | awk '/default/ {print \$5}')/address") ?: null;
    }

    public static function startBatch(): string
    {
        return static::$batchUuid = (string) Str::uuid();
    }

    public static function stopBatch(): void
    {
        static::$batchUuid = null;
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'subject');
    }

    public function withoutLogging(callable $callback)
    {
        $wasLogging = $this->isLogging();
        $this->disableLogging();

        try {
            return $callback($this);
        } finally {
            if ($wasLogging) {
                $this->enableLogging();
            }
        }
    }

    public function enableLogging(): self
    {
        static::$logOnlyDirty = true;
        return $this;
    }

    public function disableLogging(): self
    {
        static::$logOnlyDirty = false;
        return $this;
    }

    public function isLogging(): bool
    {
        return static::$logOnlyDirty;
    }
}

