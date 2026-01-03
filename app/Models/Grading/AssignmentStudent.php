<?php

namespace App\Models\Grading;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\StudentDetails\StudentInfo;

/**
 * AssignmentStudent Pivot Model
 * 
 * This model represents the many-to-many relationship between
 * Assignments and Students (StudentInfo).
 */
class AssignmentStudent extends Pivot
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'assignment_student';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'assignment_id',
        'student_info_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'assignment_id' => 'integer',
        'student_info_id' => 'integer',
    ];

    /**
     * Get the assignment that this pivot belongs to.
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    /**
     * Get the student info that this pivot belongs to.
     */
    public function studentInfo(): BelongsTo
    {
        return $this->belongsTo(StudentInfo::class, 'student_info_id');
    }
}

