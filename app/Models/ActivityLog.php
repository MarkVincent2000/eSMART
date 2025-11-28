<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'log_name',
        'description',
        'subject_type',
        'subject_id',
        'causer_type',
        'causer_id',
        'properties',
        'old_values',
        'new_values',
        'event',
        'batch_uuid',
        'ip_address',
        'mac_address',
        'user_agent',
        'address',
        'latitude',
        'longitude'
    ];

    protected $casts = [
        'properties' => 'array',
        'old_values' => 'array',
        'new_values' => 'array'
    ];

    /**
     * Get the subject model of the activity log.
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the causer model of the activity log.
     */
    public function causer(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to only include logs for a specific model.
     */
    public function scopeForSubject($query, Model $subject)
    {
        return $query->where('subject_type', get_class($subject))
                    ->where('subject_id', $subject->getKey());
    }

    /**
     * Scope a query to only include logs caused by a specific model.
     */
    public function scopeCausedBy($query, Model $causer)
    {
        return $query->where('causer_type', get_class($causer))
                    ->where('causer_id', $causer->getKey());
    }

    /**
     * Scope a query to only include logs with a specific event.
     */
    public function scopeWithEvent($query, string $event)
    {
        return $query->where('event', $event);
    }

    /**
     * Scope a query to only include logs with a specific log name.
     */
    public function scopeInLog($query, string $logName)
    {
        return $query->where('log_name', $logName);
    }
}