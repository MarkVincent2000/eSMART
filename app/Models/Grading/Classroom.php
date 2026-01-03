<?php

namespace App\Models\Grading;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LoggerTrait;
use App\Models\StudentDetails\Semester;
use App\Models\StudentDetails\Section;
use App\Models\StudentDetails\StudentInfo;
use App\Models\User;
use App\Models\Subject;
use App\Models\Grading\Assignment;
use App\Models\Grading\Grade;

class Classroom extends Model
{
    use HasFactory, LoggerTrait, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'classrooms';

    /**
     * Classroom status constants.
     */
    public const STATUS_ACTIVE = 'active';
    public const STATUS_ARCHIVED = 'archived';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'class_code',
        'description',
        'subject_id',
        'subject_type',
        'section_id',
        'semester_id',
        'room',
        'status',
        'allow_student_posts',
        'allow_student_comments',
        'students_can_see_each_other',
        'guardians_can_see_updates',
        'created_by',
        'archived_at',
        'completed_at',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'subject_id' => 'integer',
        'section_id' => 'integer',
        'semester_id' => 'integer',
        'allow_student_posts' => 'boolean',
        'allow_student_comments' => 'boolean',
        'students_can_see_each_other' => 'boolean',
        'guardians_can_see_updates' => 'boolean',
        'created_by' => 'integer',
        'archived_at' => 'datetime',
        'completed_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate class code if not provided
        static::creating(function ($classroom) {
            if (empty($classroom->class_code)) {
                $classroom->class_code = static::generateClassCode();
            }
        });
    }

    /**
     * Generate a unique class code.
     *
     * @return string
     */
    public static function generateClassCode(): string
    {
        do {
            $code = strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));
        } while (static::where('class_code', $code)->exists());

        return $code;
    }

    /**
     * Get the subject (polymorphic relationship).
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the section associated with this classroom.
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * Get the semester associated with this classroom.
     */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * Get the teacher who created this classroom.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all students enrolled in this classroom.
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(StudentInfo::class, 'classroom_student', 'classroom_id', 'student_info_id')
            ->using(ClassroomStudent::class)
            ->withPivot('enrolled_at', 'role', 'status')
            ->withTimestamps();
    }

    /**
     * Get all assignments for this classroom.
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    /**
     * Get all grades for this classroom.
     * Grades are linked through matching subject, section, and semester.
     */
    public function grades()
    {
        return Grade::where('subject_id', $this->subject_id)
            ->where('subject_type', $this->subject_type)
            ->where('section_id', $this->section_id)
            ->where('semester_id', $this->semester_id);
    }

    /**
     * Check if classroom is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if classroom is archived.
     *
     * @return bool
     */
    public function isArchived(): bool
    {
        return $this->status === self::STATUS_ARCHIVED;
    }

    /**
     * Archive this classroom.
     *
     * @return bool
     */
    public function archive(): bool
    {
        return $this->update([
            'status' => self::STATUS_ARCHIVED,
            'archived_at' => now(),
        ]);
    }

    /**
     * Complete this classroom.
     *
     * @return bool
     */
    public function complete(): bool
    {
        return $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);
    }

    /**
     * Activate this classroom.
     *
     * @return bool
     */
    public function activate(): bool
    {
        return $this->update([
            'status' => self::STATUS_ACTIVE,
            'archived_at' => null,
            'completed_at' => null,
        ]);
    }

    /**
     * Scope a query to only include active classrooms.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope a query to only include archived classrooms.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeArchived($query)
    {
        return $query->where('status', self::STATUS_ARCHIVED);
    }

    /**
     * Scope a query to filter by semester.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $semesterId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForSemester($query, int $semesterId)
    {
        return $query->where('semester_id', $semesterId);
    }

    /**
     * Scope a query to filter by section.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $sectionId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForSection($query, int $sectionId)
    {
        return $query->where('section_id', $sectionId);
    }

    /**
     * Scope a query to filter by subject.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $subjectId
     * @param string|null $subjectType
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForSubject($query, int $subjectId, ?string $subjectType = null)
    {
        $query->where('subject_id', $subjectId);
        
        if ($subjectType) {
            $query->where('subject_type', $subjectType);
        }
        
        return $query;
    }

    /**
     * Get the display name with code.
     *
     * @return string
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->name} ({$this->class_code})";
    }
}
