<?php

namespace App\Models\StudentDetails;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\YearLevel;

/**
 * ProgramYearLevel Pivot Model
 * 
 * This model represents the many-to-many relationship between
 * Programs and Year Levels.
 */
class ProgramYearLevel extends Pivot
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'program_year_level';

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
        'program_id',
        'year_level',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'program_id' => 'integer',
        'year_level' => YearLevel::class,
    ];

    /**
     * Get the program that this pivot belongs to.
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }
}

