<?php

namespace App\Models\Teacher;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LoggerTrait;
use App\Models\Grading\Classroom;
use App\Models\Grading\Grade;
use App\Models\StudentDetails\Section;
use App\Models\StudentDetails\Semester;
use App\Models\Subject;

/**
 * Workload model.
 *
 * Represents a single teaching load entry for a teacher: one subject
 * in a specific section and semester, optionally linked to a classroom.
 *
 * This is the core of the teacher's workload: each record corresponds
 * to the subjects and sections they handle and provides a hook to
 * associated grades.
 *
 * NOTE: The underlying database table (teacher_assignments) and any
 * foreign keys should be created via migrations separately.
 */
class Workload extends Model
{
    use HasFactory, LoggerTrait, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'teacher_assignments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [ 
        'teacher_id',
        'classroom_id',
        'subject_id',
        'subject_type',
        'section_id',
        'semester_id',
        'load_units',
        'schedule',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'teacher_id' => 'integer',
        'classroom_id' => 'integer',
        'subject_id' => 'integer',
        'section_id' => 'integer',
        'semester_id' => 'integer',
        'load_units' => 'decimal:2',
        'schedule' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Get the teacher who owns this workload.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Get the classroom associated with this workload (if any).
     */
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    /**
     * Get the subject associated with this workload.
     *
     * This uses a concrete Subject model for now; if you later
     * need polymorphic subjects (like Grade::subject), you can
     * switch this to a MorphTo relationship and rely on subject_type.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the section this workload is for.
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * Get the semester this workload belongs to.
     */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * Get all grades tied to this workload.
     *
     * We link on subject_id, section_id, and semester_id so you can
     * easily query all grades for this teaching load.
     */
    public function grades()
    {
        return Grade::where('subject_id', $this->subject_id)
            ->where('section_id', $this->section_id)
            ->where('semester_id', $this->semester_id);
    }

    /**
     * Human-readable display label for UI usage.
     */
    public function getDisplayNameAttribute(): string
    {
        $parts = [];

        if ($this->subject) {
            $parts[] = $this->subject->name ?? 'Subject';
        }

        if ($this->section) {
            $parts[] = 'Section ' . ($this->section->name ?? $this->section->id);
        }

        if ($this->semester) {
            $parts[] = $this->semester->name ?? 'Semester ' . $this->semester->id;
        }

        return implode(' • ', $parts) ?: 'Workload #' . $this->id;
    }
}

