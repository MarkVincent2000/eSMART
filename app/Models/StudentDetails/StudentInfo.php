<?php

namespace App\Models\StudentDetails;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;
use App\Traits\LoggerTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentInfo extends Model
{
    use HasFactory, LoggerTrait, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'student_infos';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'student_number',
        'program_id',
        'year_level',
        'section_id',
        'semester',
        'school_year',
        'status',
        'enrolled_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'user_id' => 'integer',
        'program_id' => 'integer',
        'section_id' => 'integer',
        // semester is stored as JSON
        'semester' => 'array',
        'year_level' => 'integer',
        'enrolled_at' => 'date',
    ];

    /**
     * Get the user that owns the student info.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the program associated with the student.
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Get the section associated with the student.
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * Get all assignments assigned to this student.
     */
    public function assignments(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Grading\Assignment::class, 'assignment_student', 'student_info_id', 'assignment_id')
            ->using(\App\Models\Grading\AssignmentStudent::class)
            ->withTimestamps();
    }

    /**
     * Get all classrooms this student is enrolled in.
     */
    public function classrooms(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Grading\Classroom::class, 'classroom_student', 'student_info_id', 'classroom_id')
            ->using(\App\Models\Grading\ClassroomStudent::class)
            ->withPivot('role', 'status', 'enrolled_at')
            ->withTimestamps();
    }

    /**
     * Get all grades for this student.
     */
    public function grades(): HasMany
    {
        return $this->hasMany(\App\Models\Grading\Grade::class);
    }

    // Note:
    // - semester_id has been replaced with a JSON "semester" field.
    // - quarter_id has been removed. Use semester (JSON) and school_year
    //   to determine the active term instead of quarters.

    /**
     * Get a human-readable list of semester names from the JSON field.
     */
    public function getSemesterNamesAttribute(): string
    {
        if (empty($this->semester) || !is_array($this->semester)) {
            return 'N/A';
        }

        $names = array_column($this->semester, 'name');

        if (empty($names)) {
            return 'N/A';
        }

        return implode(' / ', $names);
    }
}

