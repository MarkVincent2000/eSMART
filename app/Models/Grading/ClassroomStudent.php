<?php

namespace App\Models\Grading;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\StudentDetails\StudentInfo;

/**
 * ClassroomStudent Pivot Model
 * 
 * This model represents the many-to-many relationship between
 * Classrooms and Students (StudentInfo).
 */
class ClassroomStudent extends Pivot
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'classroom_student';

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
        'classroom_id',
        'student_info_id',
        'role',
        'status',
        'enrolled_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'classroom_id' => 'integer',
        'student_info_id' => 'integer',
        'enrolled_at' => 'datetime',
    ];

    /**
     * Get the classroom that this pivot belongs to.
     */
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    /**
     * Get the student info that this pivot belongs to.
     */
    public function studentInfo(): BelongsTo
    {
        return $this->belongsTo(StudentInfo::class, 'student_info_id');
    }
}

