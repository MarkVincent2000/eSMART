<?php

namespace App\Models\StudentDetails;    

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Enums\YearLevel;
use App\Traits\LoggerTrait;
use App\Models\Event\Event;
use App\Models\Event\EventSection;

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

    /**
     * Get all events associated with this section.
     */
    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_section')
            ->using(EventSection::class)
            ->withTimestamps();
    }
}

