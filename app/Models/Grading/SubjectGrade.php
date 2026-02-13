<?php

namespace App\Models\Grading;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LoggerTrait;
use App\Models\Subject;
use App\Models\StudentDetails\Semester;

/**
 * SubjectGrade Model
 * 
 * Represents a student's grade for a specific subject within a semester.
 * This is a pivot/relationship model connecting StudentInfoGrade to Subject.
 */
class SubjectGrade extends Model
{
    use HasFactory, LoggerTrait, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'subject_grades';

    /**
     * The attributes that are mass assignable.
     * One row per subject; grade_type is JSON: quarter_1..4 or midterm/final_term + final_grade + remarks.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'student_info_grade_id',
        'subject_id',
        'subject_name',
        'semester_id',
        'semester_type',
        'grade_type',
        'is_quarter',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'student_info_grade_id' => 'integer',
        'subject_id' => 'integer',
        'semester_id' => 'integer',
        'is_quarter' => 'boolean',
        'grade_type' => 'array',
    ];

    /**
     * Get the student info grade associated with this subject grade.
     */
    public function studentInfoGrade(): BelongsTo
    {
        return $this->belongsTo(StudentInfoGrade::class, 'student_info_grade_id');
    }

    /**
     * Get the subject associated with this grade.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the semester associated with this grade.
     */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }
}
