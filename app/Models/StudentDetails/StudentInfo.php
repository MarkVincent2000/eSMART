<?php

namespace App\Models\StudentDetails;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Traits\LoggerTrait;

class StudentInfo extends Model
{
    use HasFactory, LoggerTrait;

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
        'semester_id',
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
        'semester_id' => 'integer',
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
     * Get the semester associated with the student.
     */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }
}

