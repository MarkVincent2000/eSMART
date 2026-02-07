<?php

namespace App\Models\Grading;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LoggerTrait;
use App\Models\StudentDetails\Semester;
use App\Models\StudentDetails\Quarter;
use App\Models\StudentDetails\Section;
use App\Models\StudentDetails\StudentInfo;
use App\Models\User;
use App\Models\Grading\Classroom;
use App\Models\Comment;

class Assignment extends Model
{
    use HasFactory, LoggerTrait, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'assignments';

    /**
     * Assignment type constants.
     */
    public const TYPE_ASSIGNMENT = 'assignment';
    public const TYPE_PROJECT = 'project';
    public const TYPE_HOMEWORK = 'homework';
    public const TYPE_ACTIVITY = 'activity';
    public const TYPE_LABORATORY = 'laboratory';
    public const TYPE_RESEARCH = 'research';
    public const TYPE_PRESENTATION = 'presentation';
    public const TYPE_OTHER = 'other';

    /**
     * Assignment status constants.
     */
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'instructions',
        'assignment_type',
        'subject_id',
        'subject_type', // For polymorphic relationship
        'section_id',
        'semester_id',
        'quarter_id',
        'classroom_id', // Direct relationship to classroom
        'component_category', // DepEd component: written_works, performance_tasks
        'points_possible',
        'weight', // Weight percentage within the component
        'due_date',
        'due_time',
        'allow_late_submission',
        'late_penalty_percentage', // Percentage deduction per day late
        'max_late_days', // Maximum days after due date that submission is accepted
        'status',
        'sms_notification',
        'is_required',
        'is_group_assignment',
        'max_group_size',
        'created_by',
        'published_at',
        'closed_at',
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
        'quarter_id' => 'integer',
        'classroom_id' => 'integer',
        'points_possible' => 'decimal:2',
        'weight' => 'decimal:2',
        'due_date' => 'date',
        'due_time' => 'datetime',
        'allow_late_submission' => 'boolean',
        'late_penalty_percentage' => 'decimal:2',
        'max_late_days' => 'integer',
        'is_required' => 'boolean',
        'is_group_assignment' => 'boolean',
        'max_group_size' => 'integer',
        'sms_notification' => 'boolean',
        'created_by' => 'integer',
        'published_at' => 'datetime',
        'closed_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the subject (polymorphic relationship).
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the section associated with this assignment.
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * Get the semester associated with this assignment.
     */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * Get the quarter associated with this assignment.
     */
    public function quarter(): BelongsTo
    {
        return $this->belongsTo(Quarter::class);
    }

    /**
     * Get the teacher who created this assignment.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all attachments for this assignment.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(AssignmentAttachment::class);
    }

    /**
     * Get all submissions for this assignment.
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    /**
     * Get all grades related to this assignment.
     */
    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class, 'assignment_id');
    }

    /**
     * Get all students assigned to this assignment.
     */
    /**
     * Get all comments for this assignment.
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')
            ->whereNull('parent_id') // Only top-level comments
            ->where('status', Comment::STATUS_PUBLISHED)
            ->orderBy('created_at', 'desc');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(StudentInfo::class, 'assignment_student', 'assignment_id', 'student_info_id')
            ->using(AssignmentStudent::class)
            ->withTimestamps();
    }

    /**
     * Get the classroom this assignment belongs to.
     */
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    /**
     * Check if assignment is published.
     *
     * @return bool
     */
    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED && $this->published_at !== null;
    }

    /**
     * Check if assignment is closed.
     *
     * @return bool
     */
    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED || 
               ($this->closed_at !== null && $this->closed_at->isPast());
    }

    /**
     * Check if assignment is past due date.
     *
     * @return bool
     */
    public function isPastDue(): bool
    {
        if (!$this->due_date) {
            return false;
        }

        $dueDateTime = $this->due_time ?? $this->due_date->endOfDay();
        return now()->isAfter($dueDateTime);
    }

    /**
     * Check if late submission is still allowed.
     *
     * @return bool
     */
    public function allowsLateSubmission(): bool
    {
        if (!$this->allow_late_submission || !$this->isPastDue()) {
            return false;
        }

        if ($this->max_late_days === null) {
            return true; // No limit on late days
        }

        $daysPastDue = now()->diffInDays($this->due_date, false);
        return abs($daysPastDue) <= $this->max_late_days;
    }

    /**
     * Calculate late penalty percentage for a submission date.
     *
     * @param \Carbon\Carbon $submissionDate
     * @return float
     */
    public function calculateLatePenalty(\Carbon\Carbon $submissionDate): float
    {
        if (!$this->isPastDue() || !$this->allow_late_submission) {
            return 0;
        }

        $dueDateTime = $this->due_time ?? $this->due_date->endOfDay();
        $daysLate = $submissionDate->diffInDays($dueDateTime, false);

        if ($daysLate <= 0) {
            return 0;
        }

        // Calculate penalty: days late × penalty percentage
        $penalty = $daysLate * ($this->late_penalty_percentage ?? 0);

        // Cap at 100% (full deduction)
        return min($penalty, 100);
    }

    /**
     * Publish the assignment.
     *
     * @return bool
     */
    public function publish(): bool
    {
        return $this->update([
            'status' => self::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);
    }

    /**
     * Close the assignment.
     *
     * @return bool
     */
    public function close(): bool
    {
        return $this->update([
            'status' => self::STATUS_CLOSED,
            'closed_at' => now(),
        ]);
    }

    /**
     * Get all available assignment types.
     *
     * @return array
     */
    public static function getAssignmentTypes(): array
    {
        return [
            self::TYPE_ASSIGNMENT,
            self::TYPE_PROJECT,
            self::TYPE_HOMEWORK,
            self::TYPE_ACTIVITY,
            self::TYPE_LABORATORY,
            self::TYPE_RESEARCH,
            self::TYPE_PRESENTATION,
            self::TYPE_OTHER,
        ];
    }

    /**
     * Get all available statuses.
     *
     * @return array
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_PUBLISHED,
            self::STATUS_CLOSED,
            self::STATUS_CANCELLED,
        ];
    }

    /**
     * Scope a query to only include published assignments.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED)
            ->whereNotNull('published_at');
    }

    /**
     * Scope a query to only include active (published and not closed) assignments.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED)
            ->where(function ($q) {
                $q->whereNull('closed_at')
                  ->orWhere('closed_at', '>', now());
            });
    }

    /**
     * Scope a query to filter by component category.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $componentCategory
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForComponent($query, string $componentCategory)
    {
        return $query->where('component_category', $componentCategory);
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
     * Scope a query to filter by quarter.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $quarterId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForQuarter($query, int $quarterId)
    {
        return $query->where('quarter_id', $quarterId);
    }

    /**
     * Scope a query to filter by creator.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCreator($query, int $userId)
    {
        return $query->where('created_by', $userId);
    }
}

