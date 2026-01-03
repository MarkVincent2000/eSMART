<?php

namespace App\Models\Grading;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LoggerTrait;
use App\Models\StudentDetails\StudentInfo;
use App\Models\StudentDetails\Semester;
use App\Models\StudentDetails\Quarter;
use App\Models\StudentDetails\Section;
use App\Models\User;

class Grade extends Model
{
    use HasFactory, LoggerTrait, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'grades';

    /**
     * DepEd K-12 Component Categories.
     * These are the main components used in quarterly grade calculation.
     */
    public const COMPONENT_WRITTEN_WORKS = 'written_works';
    public const COMPONENT_PERFORMANCE_TASKS = 'performance_tasks';
    public const COMPONENT_QUARTERLY_ASSESSMENT = 'quarterly_assessment';

    /**
     * Grade type constants.
     * These are specific assessment types within each component.
     */
    public const TYPE_QUIZ = 'quiz';
    public const TYPE_EXAM = 'exam';
    public const TYPE_MIDTERM = 'midterm';
    public const TYPE_FINAL = 'final';
    public const TYPE_ASSIGNMENT = 'assignment';
    public const TYPE_PROJECT = 'project';
    public const TYPE_LABORATORY = 'laboratory';
    public const TYPE_PARTICIPATION = 'participation';
    public const TYPE_ATTENDANCE = 'attendance';
    public const TYPE_HOMEWORK = 'homework';
    public const TYPE_ACTIVITY = 'activity';
    public const TYPE_OTHER = 'other';

    /**
     * DepEd K-12 Minimum passing grade.
     */
    public const MINIMUM_PASSING_GRADE = 75.0;

    /**
     * Grade status constants.
     */
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING = 'pending';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_FINALIZED = 'finalized';
    public const STATUS_LOCKED = 'locked';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'student_info_id',
        'semester_id',
        'quarter_id',
        'section_id',
        'subject_id',
        'subject_type', // For polymorphic relationship (if Subject model doesn't exist yet)
        'assignment_id', // Link to assignment if grade is from an assignment
        'component_category', // DepEd component: written_works, performance_tasks, quarterly_assessment
        'grade_type',
        'grade_value',
        'percentage',
        'letter_grade',
        'points_earned',
        'points_possible',
        'max_score',
        'weight', // Weight percentage for this grade within its component (e.g., 10% of written works)
        'status',
        'remarks',
        'comments',
        'graded_at',
        'graded_by',
        'approved_by',
        'approved_at',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'student_info_id' => 'integer',
        'semester_id' => 'integer',
        'quarter_id' => 'integer',
        'section_id' => 'integer',
        'subject_id' => 'integer',
        'assignment_id' => 'integer',
        'grade_value' => 'decimal:2',
        'percentage' => 'decimal:2',
        'points_earned' => 'decimal:2',
        'points_possible' => 'decimal:2',
        'max_score' => 'decimal:2',
        'weight' => 'decimal:2',
        'graded_at' => 'datetime',
        'graded_by' => 'integer',
        'approved_by' => 'integer',
        'approved_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the student info associated with this grade.
     */
    public function studentInfo(): BelongsTo
    {
        return $this->belongsTo(StudentInfo::class);
    }

    /**
     * Get the semester associated with this grade.
     */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * Get the quarter associated with this grade.
     */
    public function quarter(): BelongsTo
    {
        return $this->belongsTo(Quarter::class);
    }

    /**
     * Get the section associated with this grade.
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * Get the subject (polymorphic relationship).
     * This allows flexibility if Subject model doesn't exist yet.
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who graded this.
     */
    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    /**
     * Get the user who approved this grade.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the assignment associated with this grade (if applicable).
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    /**
     * Calculate percentage from points if not set.
     *
     * @return float|null
     */
    public function calculatePercentage(): ?float
    {
        if ($this->points_possible > 0 && $this->points_earned !== null) {
            return round(($this->points_earned / $this->points_possible) * 100, 2);
        }

        if ($this->max_score > 0 && $this->grade_value !== null) {
            return round(($this->grade_value / $this->max_score) * 100, 2);
        }

        return $this->percentage;
    }

    /**
     * Get letter grade based on DepEd K-12 grading scale.
     *
     * @param float|null $percentage
     * @return string|null
     */
    public function getLetterGrade(?float $percentage = null): ?string
    {
        $percentage = $percentage ?? $this->calculatePercentage();

        if ($percentage === null) {
            return $this->letter_grade;
        }

        // DepEd K-12 Grading Scale (Grades 7-12)
        if ($percentage >= 90 && $percentage <= 100) return 'O'; // Outstanding
        if ($percentage >= 85 && $percentage <= 89) return 'VS'; // Very Satisfactory
        if ($percentage >= 80 && $percentage <= 84) return 'S'; // Satisfactory
        if ($percentage >= 75 && $percentage <= 79) return 'FS'; // Fairly Satisfactory
        return 'D'; // Did Not Meet Expectations (Below 75)
    }

    /**
     * Get descriptive grade based on DepEd K-12 grading scale.
     *
     * @param float|null $percentage
     * @return string|null
     */
    public function getDescriptiveGrade(?float $percentage = null): ?string
    {
        $percentage = $percentage ?? $this->calculatePercentage();

        if ($percentage === null) {
            return null;
        }

        // DepEd K-12 Descriptive Grades
        if ($percentage >= 90 && $percentage <= 100) return 'Outstanding';
        if ($percentage >= 85 && $percentage <= 89) return 'Very Satisfactory';
        if ($percentage >= 80 && $percentage <= 84) return 'Satisfactory';
        if ($percentage >= 75 && $percentage <= 79) return 'Fairly Satisfactory';
        return 'Did Not Meet Expectations';
    }

    /**
     * Check if grade is passing (75% or above).
     *
     * @param float|null $percentage
     * @return bool
     */
    public function isPassing(?float $percentage = null): bool
    {
        $percentage = $percentage ?? $this->calculatePercentage();
        return $percentage !== null && $percentage >= self::MINIMUM_PASSING_GRADE;
    }

    /**
     * Check if grade is finalized.
     *
     * @return bool
     */
    public function isFinalized(): bool
    {
        return $this->status === self::STATUS_FINALIZED || $this->status === self::STATUS_LOCKED;
    }

    /**
     * Check if grade is published.
     *
     * @return bool
     */
    public function isPublished(): bool
    {
        return in_array($this->status, [self::STATUS_PUBLISHED, self::STATUS_FINALIZED]);
    }

    /**
     * Check if grade can be edited.
     *
     * @return bool
     */
    public function canBeEdited(): bool
    {
        return !in_array($this->status, [self::STATUS_FINALIZED, self::STATUS_LOCKED]);
    }

    /**
     * Approve the grade.
     *
     * @param int $approvedBy
     * @return bool
     */
    public function approve(int $approvedBy): bool
    {
        return $this->update([
            'approved_by' => $approvedBy,
            'approved_at' => now(),
            'status' => self::STATUS_PUBLISHED,
        ]);
    }

    /**
     * Finalize the grade.
     *
     * @return bool
     */
    public function finalize(): bool
    {
        // Auto-calculate percentage and letter grade if not set
        if ($this->percentage === null) {
            $this->percentage = $this->calculatePercentage();
        }

        if ($this->letter_grade === null && $this->percentage !== null) {
            $this->letter_grade = $this->getLetterGrade();
        }

        return $this->update([
            'status' => self::STATUS_FINALIZED,
        ]);
    }

    /**
     * Lock the grade (prevents further editing).
     *
     * @return bool
     */
    public function lock(): bool
    {
        return $this->update([
            'status' => self::STATUS_LOCKED,
        ]);
    }

    /**
     * Get all available DepEd component categories.
     *
     * @return array
     */
    public static function getComponentCategories(): array
    {
        return [
            self::COMPONENT_WRITTEN_WORKS,
            self::COMPONENT_PERFORMANCE_TASKS,
            self::COMPONENT_QUARTERLY_ASSESSMENT,
        ];
    }

    /**
     * Get all available grade types.
     *
     * @return array
     */
    public static function getGradeTypes(): array
    {
        return [
            self::TYPE_QUIZ,
            self::TYPE_EXAM,
            self::TYPE_MIDTERM,
            self::TYPE_FINAL,
            self::TYPE_ASSIGNMENT,
            self::TYPE_PROJECT,
            self::TYPE_LABORATORY,
            self::TYPE_PARTICIPATION,
            self::TYPE_ATTENDANCE,
            self::TYPE_HOMEWORK,
            self::TYPE_ACTIVITY,
            self::TYPE_OTHER,
        ];
    }

    /**
     * Calculate quarterly grade from weighted components.
     * 
     * Formula: (Written Works × Weight) + (Performance Tasks × Weight) + (Quarterly Assessment × Weight)
     * 
     * @param int $studentInfoId
     * @param int $subjectId
     * @param int $quarterId
     * @param array $componentWeights Optional. Default: ['written_works' => 30, 'performance_tasks' => 30, 'quarterly_assessment' => 40]
     * @return float|null
     */
    public static function calculateQuarterlyGrade(
        int $studentInfoId,
        int $subjectId,
        int $quarterId,
        array $componentWeights = null
    ): ?float {
        // Default DepEd weights (can be customized per subject, 20-40% range)
        $defaultWeights = [
            self::COMPONENT_WRITTEN_WORKS => 30,
            self::COMPONENT_PERFORMANCE_TASKS => 30,
            self::COMPONENT_QUARTERLY_ASSESSMENT => 40,
        ];

        $weights = $componentWeights ?? $defaultWeights;

        // Validate weights sum to 100
        if (array_sum($weights) != 100) {
            throw new \InvalidArgumentException('Component weights must sum to 100%');
        }

        $quarterlyGrade = 0;
        $hasData = false;

        foreach ($weights as $component => $weight) {
            // Get average percentage for this component
            $componentAverage = self::calculateComponentAverage(
                $studentInfoId,
                $subjectId,
                $quarterId,
                $component
            );

            if ($componentAverage !== null) {
                $quarterlyGrade += ($componentAverage * $weight / 100);
                $hasData = true;
            }
        }

        return $hasData ? round($quarterlyGrade, 2) : null;
    }

    /**
     * Calculate average percentage for a specific component.
     *
     * @param int $studentInfoId
     * @param int $subjectId
     * @param int $quarterId
     * @param string $componentCategory
     * @return float|null
     */
    public static function calculateComponentAverage(
        int $studentInfoId,
        int $subjectId,
        int $quarterId,
        string $componentCategory
    ): ?float {
        $grades = self::where('student_info_id', $studentInfoId)
            ->where('subject_id', $subjectId)
            ->where('quarter_id', $quarterId)
            ->where('component_category', $componentCategory)
            ->whereNotNull('percentage')
            ->get();

        if ($grades->isEmpty()) {
            return null;
        }

        // If weights are specified, calculate weighted average
        $totalWeight = $grades->sum('weight');
        if ($totalWeight > 0) {
            $weightedSum = $grades->sum(function ($grade) {
                return ($grade->percentage ?? 0) * ($grade->weight ?? 1);
            });
            return round($weightedSum / $totalWeight, 2);
        }

        // Otherwise, calculate simple average
        return round($grades->avg('percentage'), 2);
    }

    /**
     * Calculate final grade from quarterly grades.
     * Simple average of all four quarters.
     *
     * @param int $studentInfoId
     * @param int $subjectId
     * @param int $semesterId
     * @return float|null
     */
    public static function calculateFinalGrade(
        int $studentInfoId,
        int $subjectId,
        int $semesterId
    ): ?float {
        // Get all quarters for this semester
        $quarters = \App\Models\StudentDetails\Quarter::where('semester_id', $semesterId)
            ->pluck('id')
            ->toArray();

        if (empty($quarters)) {
            return null;
        }

        $quarterlyGrades = [];
        foreach ($quarters as $quarterId) {
            $quarterlyGrade = self::calculateQuarterlyGrade($studentInfoId, $subjectId, $quarterId);
            if ($quarterlyGrade !== null) {
                $quarterlyGrades[] = $quarterlyGrade;
            }
        }

        if (empty($quarterlyGrades)) {
            return null;
        }

        return round(array_sum($quarterlyGrades) / count($quarterlyGrades), 2);
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
            self::STATUS_PENDING,
            self::STATUS_PUBLISHED,
            self::STATUS_FINALIZED,
            self::STATUS_LOCKED,
        ];
    }

    /**
     * Scope a query to only include finalized grades.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFinalized($query)
    {
        return $query->whereIn('status', [self::STATUS_FINALIZED, self::STATUS_LOCKED]);
    }

    /**
     * Scope a query to only include published grades.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublished($query)
    {
        return $query->whereIn('status', [self::STATUS_PUBLISHED, self::STATUS_FINALIZED]);
    }

    /**
     * Scope a query to only include editable grades.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEditable($query)
    {
        return $query->whereNotIn('status', [self::STATUS_FINALIZED, self::STATUS_LOCKED]);
    }

    /**
     * Scope a query to filter by grade type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('grade_type', $type);
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
     * Scope a query to filter by subject.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $subjectId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForSubject($query, int $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }
}

