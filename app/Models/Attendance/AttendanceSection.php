<?php

namespace App\Models\Attendance;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\StudentDetails\Section;

/**
 * AttendanceSection Pivot Model
 * 
 * This model represents the many-to-many relationship between
 * Attendance sessions and Sections.
 */
class AttendanceSection extends Pivot
{

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
        'attendance_id',
        'section_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'attendance_id' => 'integer',
        'section_id' => 'integer',
    ];

    /**
     * Get the attendance session that this pivot belongs to.
     */
    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    /**
     * Get the section that this pivot belongs to.
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }
}
