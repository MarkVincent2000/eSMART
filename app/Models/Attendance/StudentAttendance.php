<?php

namespace App\Models\Attendance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use App\Traits\LoggerTrait;
use App\Models\User;

class StudentAttendance extends Model
{
    use HasFactory, LoggerTrait, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'student_attendances';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'attendance_id',
        'user_id',
        'student_info_id',
        'status',
        'check_in_time',
        'check_out_time',
        'duration_minutes',
        'is_late',
        'is_excused',
        'excuse_reason',
        'remarks',
        'notes',
        'location',
        'latitude',
        'longitude',
        'ip_address',
        'device_info',
        'metadata',
        'marked_by',
        'approved_by',
        'approved_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'attendance_id' => 'integer',
        'user_id' => 'integer',
        'student_info_id' => 'integer',
        'check_in_time' => 'string',  // TIME data type - stores only time (HH:mm:ss)
        'check_out_time' => 'string', // TIME data type - stores only time (HH:mm:ss)
        'duration_minutes' => 'integer',
        'is_late' => 'boolean',
        'is_excused' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'metadata' => 'array',
        'marked_by' => 'integer',
        'approved_by' => 'integer',
        'approved_at' => 'datetime',
    ];

    /**
     * Get check_in_time combined with attendance date as Carbon instance (for comparisons/calculations)
     *
     * @return \Carbon\Carbon|null
     */
    public function getCheckInTimeAttribute($value)
    {
        // If no time value stored, return null
        if (!$value || !isset($this->attributes['check_in_time'])) {
            return null;
        }

        // Get the raw time string from attributes
        $timeStr = $this->attributes['check_in_time'];

        // Load attendance relationship if not loaded (it will lazy load)
        if (!$this->relationLoaded('attendance')) {
            $this->load('attendance');
        }

        $attendance = $this->attendance;
        if (!$attendance || !$attendance->date) {
            return null;
        }

        // Combine attendance date with time to create full datetime in Manila timezone
        $dateStr = $attendance->date instanceof \DateTimeInterface 
            ? $attendance->date->format('Y-m-d') 
            : $attendance->date;
        
        return Carbon::createFromFormat('Y-m-d H:i:s', $dateStr . ' ' . $timeStr, 'Asia/Manila');
    }

    /**
     * Set check_in_time from datetime or time string
     *
     * @param mixed $value
     * @return void
     */
    public function setCheckInTimeAttribute($value)
    {
        if (!$value) {
            $this->attributes['check_in_time'] = null;
            return;
        }

        if ($value instanceof \DateTimeInterface) {
            // Extract time portion in Manila timezone
            $manilaTime = Carbon::instance($value)->setTimezone('Asia/Manila');
            $this->attributes['check_in_time'] = $manilaTime->format('H:i:s');
        } elseif (is_string($value)) {
            // If it's already in H:i:s format, use it directly
            if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $value)) {
                $this->attributes['check_in_time'] = $value;
            } else {
                // Try to parse as datetime and extract time in Manila timezone
                try {
                    $parsed = Carbon::parse($value)->setTimezone('Asia/Manila');
                    $this->attributes['check_in_time'] = $parsed->format('H:i:s');
                } catch (\Exception $e) {
                    $this->attributes['check_in_time'] = $value;
                }
            }
        } else {
            $this->attributes['check_in_time'] = $value;
        }
    }

    /**
     * Get check_out_time combined with attendance date as Carbon instance (for comparisons/calculations)
     *
     * @return \Carbon\Carbon|null
     */
    public function getCheckOutTimeAttribute($value)
    {
        // If no time value stored, return null
        if (!$value || !isset($this->attributes['check_out_time'])) {
            return null;
        }

        // Get the raw time string from attributes
        $timeStr = $this->attributes['check_out_time'];

        // Load attendance relationship if not loaded (it will lazy load)
        if (!$this->relationLoaded('attendance')) {
            $this->load('attendance');
        }

        $attendance = $this->attendance;
        if (!$attendance || !$attendance->date) {
            return null;
        }

        // Combine attendance date with time to create full datetime in Manila timezone
        $dateStr = $attendance->date instanceof \DateTimeInterface 
            ? $attendance->date->format('Y-m-d') 
            : $attendance->date;
        
        return Carbon::createFromFormat('Y-m-d H:i:s', $dateStr . ' ' . $timeStr, 'Asia/Manila');
    }

    /**
     * Set check_out_time from datetime or time string
     *
     * @param mixed $value
     * @return void
     */
    public function setCheckOutTimeAttribute($value)
    {
        if (!$value) {
            $this->attributes['check_out_time'] = null;
            return;
        }

        if ($value instanceof \DateTimeInterface) {
            // Extract time portion in Manila timezone
            $manilaTime = Carbon::instance($value)->setTimezone('Asia/Manila');
            $this->attributes['check_out_time'] = $manilaTime->format('H:i:s');
        } elseif (is_string($value)) {
            // If it's already in H:i:s format, use it directly
            if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $value)) {
                $this->attributes['check_out_time'] = $value;
            } else {
                // Try to parse as datetime and extract time in Manila timezone
                try {
                    $parsed = Carbon::parse($value)->setTimezone('Asia/Manila');
                    $this->attributes['check_out_time'] = $parsed->format('H:i:s');
                } catch (\Exception $e) {
                    $this->attributes['check_out_time'] = $value;
                }
            }
        } else {
            $this->attributes['check_out_time'] = $value;
        }
    }

    /**
     * Attendance status constants.
     */
    public const STATUS_PRESENT = 'present';
    public const STATUS_ABSENT = 'absent';
    public const STATUS_LATE = 'late';
    public const STATUS_EXCUSED = 'excused';
    public const STATUS_PARTIAL = 'partial';
    public const STATUS_LEAVE = 'leave';
    public const STATUS_PENDING = 'pending';

    /**
     * Get the attendance session that this student attendance belongs to.
     */
    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    /**
     * Get the user (student) that this attendance record belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the student info that this attendance record belongs to.
     */
    public function studentInfo(): BelongsTo
    {
        return $this->belongsTo(\App\Models\StudentDetails\StudentInfo::class);
    }

    /**
     * Get the user who marked this attendance.
     */
    public function markedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    /**
     * Get the user who approved this attendance.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope a query to only include attendances for a specific user.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include attendances for a specific attendance session.
     */
    public function scopeForAttendance($query, int $attendanceId)
    {
        return $query->where('attendance_id', $attendanceId);
    }

    /**
     * Scope a query to only include attendances with a specific status.
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include present attendances.
     */
    public function scopePresent($query)
    {
        return $query->where('status', self::STATUS_PRESENT);
    }

    /**
     * Scope a query to only include absent attendances.
     */
    public function scopeAbsent($query)
    {
        return $query->where('status', self::STATUS_ABSENT);
    }

    /**
     * Scope a query to only include late attendances.
     */
    public function scopeLate($query)
    {
        return $query->where('is_late', true)
                    ->orWhere('status', self::STATUS_LATE);
    }

    /**
     * Scope a query to only include excused attendances.
     */
    public function scopeExcused($query)
    {
        return $query->where('is_excused', true)
                    ->orWhere('status', self::STATUS_EXCUSED);
    }

    /**
     * Scope a query to only include attendances that need approval.
     */
    public function scopePendingApproval($query)
    {
        return $query->whereNull('approved_at')
                    ->whereNotNull('marked_by');
    }

    /**
     * Scope a query to only include approved attendances.
     */
    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_at');
    }

    /**
     * Determine if the attendance is present.
     *
     * @return bool
     */
    public function isPresent(): bool
    {
        return $this->status === self::STATUS_PRESENT;
    }

    /**
     * Determine if the attendance is absent.
     *
     * @return bool
     */
    public function isAbsent(): bool
    {
        return $this->status === self::STATUS_ABSENT;
    }

    /**
     * Determine if the attendance is late.
     *
     * @return bool
     */
    public function isLate(): bool
    {
        return $this->is_late || $this->status === self::STATUS_LATE;
    }

    /**
     * Determine if the attendance is excused.
     *
     * @return bool
     */
    public function isExcused(): bool
    {
        return $this->is_excused || $this->status === self::STATUS_EXCUSED;
    }

    /**
     * Determine if the attendance is pending.
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Determine if the attendance is approved.
     *
     * @return bool
     */
    public function isApproved(): bool
    {
        return !is_null($this->approved_at);
    }

    /**
     * Calculate duration in minutes if check-in and check-out times are available.
     *
     * @return int|null
     */
    public function calculateDuration(): ?int
    {
        if ($this->check_in_time && $this->check_out_time) {
            return $this->check_in_time->diffInMinutes($this->check_out_time);
        }

        return null;
    }

    /**
     * Mark attendance as approved.
     * Note: Status determination (present/late) is now handled in the controller.
     *
     * @param int $approvedBy
     * @param string $status The status to set ('present' or 'late')
     * @param bool $isLate Whether the attendance is late
     * @return bool
     */
    public function approve(int $approvedBy, string $status = self::STATUS_PRESENT, bool $isLate = false): bool
    {
        return $this->update([
            'approved_by' => $approvedBy,
            'approved_at' => now(),
            'status' => $status,
            'is_late' => $isLate,
        ]);
    }

    /**
     * Mark attendance as unapproved.
     * Sets status back to 'pending'.
     *
     * @return bool
     */
    public function unapprove(): bool
    {
        return $this->update([
            'approved_by' => null,
            'approved_at' => null,
            'status' => self::STATUS_PENDING,
            'is_late' => false,
        ]);
    }

    /**
     * Get all available attendance statuses.
     *
     * @return array
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PRESENT,
            self::STATUS_ABSENT,
            self::STATUS_LATE,
            self::STATUS_EXCUSED,
            self::STATUS_PARTIAL,
            self::STATUS_LEAVE,
            self::STATUS_PENDING,
        ];
    }
}

