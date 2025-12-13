<?php

namespace App\Models\Attendance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LoggerTrait;
use App\Models\StudentDetails\Semester;
use App\Models\StudentDetails\Section;
use App\Models\User;

class Attendance extends Model
{
    use HasFactory, LoggerTrait, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'attendances';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'semester_id',
        'section_id',
        'attendance_type',
        'date',
        'start_time',
        'end_time',
        'scheduled_duration_minutes',
        'location',
        'latitude',
        'longitude',
        'is_active',
        'is_locked',
        'locked_at',
        'metadata',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'semester_id' => 'integer',
        'section_id' => 'integer',
        'date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'scheduled_duration_minutes' => 'integer',
        'is_active' => 'boolean',
        'is_locked' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'metadata' => 'array',
        'created_by' => 'integer',
        'locked_at' => 'datetime',
    ];

    /**
     * Attendance type constants.
     */
    public const TYPE_CLASS = 'class';
    public const TYPE_LABORATORY = 'laboratory';
    public const TYPE_LECTURE = 'lecture';
    public const TYPE_EXAM = 'exam';
    public const TYPE_EVENT = 'event';
    public const TYPE_MEETING = 'meeting';
    public const TYPE_WORKSHOP = 'workshop';
    public const TYPE_OTHER = 'other';

    /**
     * Get the semester associated with this attendance session.
     */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * Get the section associated with this attendance session.
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * Get the user who created this attendance session.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the attendable model (polymorphic relation for courses, subjects, events, etc.).
     */
    public function attendable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get all student attendance records for this attendance session.
     */
    public function studentAttendances(): HasMany
    {
        return $this->hasMany(StudentAttendance::class);
    }

    /**
     * Get all present student attendance records.
     */
    public function presentStudents(): HasMany
    {
        return $this->hasMany(StudentAttendance::class)->where('status', StudentAttendance::STATUS_PRESENT);
    }

    /**
     * Get all absent student attendance records.
     */
    public function absentStudents(): HasMany
    {
        return $this->hasMany(StudentAttendance::class)->where('status', StudentAttendance::STATUS_ABSENT);
    }

    /**
     * Scope a query to only include attendances for a specific semester.
     */
    public function scopeForSemester($query, int $semesterId)
    {
        return $query->where('semester_id', $semesterId);
    }

    /**
     * Scope a query to only include attendances for a specific section.
     */
    public function scopeForSection($query, int $sectionId)
    {
        return $query->where('section_id', $sectionId);
    }

    /**
     * Scope a query to only include attendances of a specific type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('attendance_type', $type);
    }

    /**
     * Scope a query to filter attendances by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to filter attendances for a specific date.
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    /**
     * Scope a query to only include active attendance sessions.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include locked attendance sessions.
     */
    public function scopeLocked($query)
    {
        return $query->where('is_locked', true);
    }

    /**
     * Scope a query to only include unlocked attendance sessions.
     */
    public function scopeUnlocked($query)
    {
        return $query->where('is_locked', false);
    }

    /**
     * Determine if the attendance session is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Determine if the attendance session is locked.
     *
     * @return bool
     */
    public function isLocked(): bool
    {
        return $this->is_locked;
    }

    /**
     * Lock the attendance session to prevent further modifications.
     *
     * @return bool
     */
    public function lock(): bool
    {
        return $this->update([
            'is_locked' => true,
            'locked_at' => now(),
        ]);
    }

    /**
     * Unlock the attendance session to allow modifications.
     *
     * @return bool
     */
    public function unlock(): bool
    {
        return $this->update([
            'is_locked' => false,
            'locked_at' => null,
        ]);
    }

    /**
     * Calculate scheduled duration in minutes if start and end times are available.
     *
     * @return int|null
     */
    public function calculateScheduledDuration(): ?int
    {
        if ($this->start_time && $this->end_time) {
            return $this->start_time->diffInMinutes($this->end_time);
        }

        return null;
    }

    /**
     * Get the total number of students marked present.
     *
     * @return int
     */
    public function getPresentCount(): int
    {
        return $this->studentAttendances()->where('status', StudentAttendance::STATUS_PRESENT)->count();
    }

    /**
     * Get the total number of students marked absent.
     *
     * @return int
     */
    public function getAbsentCount(): int
    {
        return $this->studentAttendances()->where('status', StudentAttendance::STATUS_ABSENT)->count();
    }

    /**
     * Get the total number of student attendance records.
     *
     * @return int
     */
    public function getTotalStudentCount(): int
    {
        return $this->studentAttendances()->count();
    }

    /**
     * Get all available attendance types.
     *
     * @return array
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_CLASS,
            self::TYPE_LABORATORY,
            self::TYPE_LECTURE,
            self::TYPE_EXAM,
            self::TYPE_EVENT,
            self::TYPE_MEETING,
            self::TYPE_WORKSHOP,
            self::TYPE_OTHER,
        ];
    }
}

