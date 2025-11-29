<?php

namespace App\Models\StudentDetails;    

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\YearLevel;
use App\Traits\LoggerTrait;

class Section extends Model
{
    use HasFactory, LoggerTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'year_level',
        'active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'year_level' => YearLevel::class,
        'active' => 'boolean',
    ];

    /**
     * Get all student infos for this section.
     */
    public function studentInfos(): HasMany
    {
        return $this->hasMany(StudentInfo::class);
    }
}

