<?php

namespace App\Models\Event;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\StudentDetails\Section;

class EventSection extends Pivot
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'event_section';

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
        'event_id',
        'section_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'event_id' => 'integer',
        'section_id' => 'integer',
    ];

    /**
     * Get the event that owns this pivot.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the section that owns this pivot.
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }
}
