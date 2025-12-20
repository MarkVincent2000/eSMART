<?php

namespace App\Models\StudentDetails;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\LoggerTrait;
use App\Enums\YearLevel;

class Program extends Model
{
    use HasFactory, LoggerTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'name',
        'active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Get all student infos for this program.
     */
    public function studentInfos(): HasMany
    {
        return $this->hasMany(StudentInfo::class);
    }

    /**
     * Get all year level pivots for this program.
     */
    public function yearLevelPivots(): HasMany
    {
        return $this->hasMany(ProgramYearLevel::class, 'program_id');
    }

    /**
     * Get year levels as an array of YearLevel enum instances.
     */
    public function getYearLevels(): array
    {
        return $this->yearLevelPivots()
            ->get()
            ->map(function($pivot) {
                return $pivot->year_level;
            })
            ->toArray();
    }

    /**
     * Get year levels as an array of integer values.
     */
    public function getYearLevelValues(): array
    {
        return $this->yearLevelPivots()
            ->get()
            ->map(function($pivot) {
                return $pivot->year_level instanceof YearLevel 
                    ? $pivot->year_level->value 
                    : $pivot->year_level;
            })
            ->toArray();
    }

    /**
     * Sync year levels for this program.
     */
    public function syncYearLevels(array $yearLevelValues): void
    {
        // Convert to YearLevel enum instances if needed
        $yearLevels = array_map(function($value) {
            return $value instanceof YearLevel ? $value : YearLevel::from($value);
        }, $yearLevelValues);

        // Delete existing pivots
        $this->yearLevelPivots()->delete();

        // Create new pivots
        foreach ($yearLevels as $yearLevel) {
            ProgramYearLevel::create([
                'program_id' => $this->id,
                'year_level' => $yearLevel,
            ]);
        }
    }
}

