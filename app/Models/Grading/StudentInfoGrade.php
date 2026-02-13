<?php

namespace App\Models\Grading;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LoggerTrait;
use App\Models\StudentDetails\StudentInfo;
use App\Models\StudentDetails\Section;
use App\Models\Teacher\Teacher;

/**
 * StudentInfoGrade Model
 * 
 * Represents a student's overall grade record for a specific school year.
 * This model stores general student information and grade eligibility status.
 */
class StudentInfoGrade extends Model
{
    use HasFactory, LoggerTrait, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'student_info_grades';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'student_info_id',
        'name',
        'school_year',
        'age',
        'sex',
        'lrn',
        'grade',
        'section',
        'date_of_birth',
        'teacher_id',
        'teacher_name',
        'date_issued',
        'eligible_to_advance_grade',
        'has_advance_unit_in',
        'has_lacking_unit_in',
        'general_average',
        'general_average_remark',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'student_info_id' => 'integer',
        'age' => 'integer',
        'grade' => 'integer',
        'teacher_id' => 'integer',
        'date_of_birth' => 'date',
        'date_issued' => 'date',
        'eligible_to_advance_grade' => 'boolean',
        'has_advance_unit_in' => 'boolean',
        'has_lacking_unit_in' => 'boolean',
        'general_average' => 'array',
        'general_average_remark' => 'array',
    ];

    /**
     * Get the student info associated with this grade.
     */
    public function studentInfo(): BelongsTo
    {
        return $this->belongsTo(StudentInfo::class);
    }

    /**
     * Get the teacher associated with this grade.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Get all subject grades for this student info grade.
     */
    public function subjectGrades(): HasMany
    {
        return $this->hasMany(SubjectGrade::class, 'student_info_grade_id');
    }
}
