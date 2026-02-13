<?php

namespace App\Models\Teacher;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LoggerTrait;
use App\Models\User;
use App\Models\Grading\Classroom;
use App\Models\Grading\Grade;
use App\Models\StudentDetails\Section;
use App\Models\StudentDetails\Semester;
use App\Models\Subject;

/**
 * Teacher domain model.
 *
 * This model represents a teacher profile that is linked 1:1 to a User record.
 * It acts as the central place to query a teacher's workload, including
 * classrooms, subjects, sections, semesters, and grades handled.
 *
 * NOTE: The underlying database table (teachers) and any foreign keys
 * should be created via migrations separately.
 */
class Teacher extends Model
{
    use HasFactory, LoggerTrait, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'teachers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'employee_no',
        'department',
        'position',
        'hire_date',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'user_id' => 'integer',
        'hire_date' => 'date',
        'metadata' => 'array',
    ];

    /**
     * Get the underlying user account for this teacher.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all classroom records created by this teacher.
     * This uses the classrooms.created_by (user_id) column.
     */
    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class, 'created_by', 'user_id');
    }

    /**
     * Get all workload entries (teaching loads) for this teacher.
     */
    public function workloads(): HasMany
    {
        return $this->hasMany(Workload::class);
    }

    /**
     * Get all subjects this teacher handles via workloads.
     */
    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(
            Subject::class,
            'teacher_assignments',
            'teacher_id',
            'subject_id'
        )->withPivot(['section_id', 'semester_id', 'classroom_id', 'load_units']);
    }

    /**
     * Get all sections this teacher handles via workloads.
     */
    public function sections(): BelongsToMany
    {
        return $this->belongsToMany(
            Section::class,
            'teacher_assignments',
            'teacher_id',
            'section_id'
        )->withPivot(['subject_id', 'semester_id', 'classroom_id', 'load_units']);
    }

    /**
     * Get all semesters this teacher teaches in via workloads.
     */
    public function semesters(): BelongsToMany
    {
        return $this->belongsToMany(
            Semester::class,
            'teacher_assignments',
            'teacher_id',
            'semester_id'
        )->withPivot(['subject_id', 'section_id', 'classroom_id', 'load_units']);
    }

    /**
     * Get all grades associated with this teacher's workload.
     *
     * This uses a has-many-through style relationship:
     * Teacher -> Workload -> Grade
     */
    public function grades(): HasManyThrough
    {
        return $this->hasManyThrough(
            Grade::class,
            Workload::class,
            'teacher_id',   // Foreign key on teacher_assignments table...
            'subject_id',   // Foreign key on grades table...
            'id',           // Local key on teachers table...
            'subject_id'    // Local key on teacher_assignments table...
        );
    }

    /**
     * Convenience accessor for full workload summary.
     *
     * Returns the total number of active workloads for this teacher.
     */
    public function getWorkloadCountAttribute(): int
    {
        return $this->workloads()->count();
    }
}

