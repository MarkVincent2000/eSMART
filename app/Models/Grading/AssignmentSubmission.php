<?php

namespace App\Models\Grading;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LoggerTrait;
use App\Models\StudentDetails\StudentInfo;
use App\Models\User;

class AssignmentSubmission extends Model
{
    use HasFactory, LoggerTrait, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'assignment_submissions';

    /**
     * Submission status constants.
     */
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_LATE = 'late';
    public const STATUS_GRADED = 'graded';
    public const STATUS_RETURNED = 'returned';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'assignment_id',
        'student_info_id',
        'submitted_by', // User ID who submitted (for group assignments)
        'content',
        'status',
        'submitted_at',
        'graded_at',
        'graded_by',
        'points_earned',
        'feedback',
        'is_late',
        'late_penalty_applied',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'assignment_id' => 'integer',
        'student_info_id' => 'integer',
        'submitted_by' => 'integer',
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
        'graded_by' => 'integer',
        'points_earned' => 'decimal:2',
        'is_late' => 'boolean',
        'late_penalty_applied' => 'decimal:2',
        'metadata' => 'array',
    ];

    /**
     * Get the assignment this submission belongs to.
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    /**
     * Get the student info associated with this submission.
     */
    public function studentInfo(): BelongsTo
    {
        return $this->belongsTo(StudentInfo::class);
    }

    /**
     * Get the user who submitted this (for group assignments).
     */
    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * Get the user who graded this submission.
     */
    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    /**
     * Get all attachments for this submission.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(AssignmentSubmissionAttachment::class, 'submission_id');
    }

    /**
     * Get the grade associated with this submission (if graded).
     * Note: This relationship is established when a grade is created from a submission.
     */
    public function grade()
    {
        return Grade::where('assignment_id', $this->assignment_id)
            ->where('student_info_id', $this->student_info_id)
            ->first();
    }

    /**
     * Check if submission is late.
     *
     * @return bool
     */
    public function isLate(): bool
    {
        if ($this->is_late !== null) {
            return $this->is_late;
        }

        if (!$this->assignment || !$this->submitted_at) {
            return false;
        }

        return $this->assignment->isPastDue() && 
               $this->submitted_at->isAfter($this->assignment->due_date);
    }

    /**
     * Calculate late penalty for this submission.
     *
     * @return float
     */
    public function calculateLatePenalty(): float
    {
        if (!$this->isLate() || !$this->assignment) {
            return 0;
        }

        return $this->assignment->calculateLatePenalty($this->submitted_at);
    }

    /**
     * Mark submission as submitted.
     *
     * @return bool
     */
    public function markAsSubmitted(): bool
    {
        $isLate = $this->isLate();
        $latePenalty = $isLate ? $this->calculateLatePenalty() : 0;

        return $this->update([
            'status' => $isLate ? self::STATUS_LATE : self::STATUS_SUBMITTED,
            'submitted_at' => now(),
            'is_late' => $isLate,
            'late_penalty_applied' => $latePenalty,
        ]);
    }

    /**
     * Grade this submission.
     *
     * @param float $pointsEarned
     * @param int $gradedBy
     * @param string|null $feedback
     * @return bool
     */
    public function gradeSubmission(float $pointsEarned, int $gradedBy, ?string $feedback = null): bool
    {
        // Apply late penalty if applicable
        $finalPoints = $pointsEarned;
        if ($this->isLate() && $this->late_penalty_applied > 0) {
            $penaltyAmount = ($pointsEarned * $this->late_penalty_applied) / 100;
            $finalPoints = max(0, $pointsEarned - $penaltyAmount);
        }

        return $this->update([
            'status' => self::STATUS_GRADED,
            'points_earned' => $finalPoints,
            'graded_at' => now(),
            'graded_by' => $gradedBy,
            'feedback' => $feedback,
        ]);
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
            self::STATUS_SUBMITTED,
            self::STATUS_LATE,
            self::STATUS_GRADED,
            self::STATUS_RETURNED,
        ];
    }

    /**
     * Scope a query to filter by assignment.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $assignmentId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForAssignment($query, int $assignmentId)
    {
        return $query->where('assignment_id', $assignmentId);
    }

    /**
     * Scope a query to filter by student.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $studentInfoId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForStudent($query, int $studentInfoId)
    {
        return $query->where('student_info_id', $studentInfoId);
    }

    /**
     * Scope a query to only include late submissions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLate($query)
    {
        return $query->where('is_late', true)
            ->orWhere('status', self::STATUS_LATE);
    }

    /**
     * Scope a query to only include graded submissions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeGraded($query)
    {
        return $query->where('status', self::STATUS_GRADED)
            ->whereNotNull('graded_at');
    }
}

