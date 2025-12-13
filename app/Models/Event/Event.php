<?php

namespace App\Models\Event;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LoggerTrait;
use App\Models\StudentDetails\Semester;
use App\Models\StudentDetails\Section;
use App\Models\User;
use App\Models\Attendance\Attendance;
use App\Models\Notification;

class Event extends Model
{
    use HasFactory, LoggerTrait, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'events';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'event_type',
        'category',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'location',
        'semester_id',
        'status',
        'image',
        'metadata',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'semester_id' => 'integer',
        'created_by' => 'integer',
        'approved_by' => 'integer',
        'approved_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Event type constants.
     */
    public const TYPE_ACADEMIC = 'academic';
    public const TYPE_SOCIAL = 'social';
    public const TYPE_SPORTS = 'sports';
    public const TYPE_CULTURAL = 'cultural';
    public const TYPE_WORKSHOP = 'workshop';
    public const TYPE_SEMINAR = 'seminar';
    public const TYPE_CONFERENCE = 'conference';
    public const TYPE_CEREMONY = 'ceremony';
    public const TYPE_MEETING = 'meeting';
    public const TYPE_OTHER = 'other';

    /**
     * Event status constants.
     */
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_POSTPONED = 'postponed';


    /**
     * Get the semester associated with this event.
     */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * Get the sections associated with this event.
     */
    public function sections(): BelongsToMany
    {
        return $this->belongsToMany(Section::class, 'event_section')
            ->using(EventSection::class)
            ->withTimestamps();
    }

    /**
     * Get the user who created this event.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who approved this event.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get all attendances related to this event.
     */
    public function attendances(): MorphMany
    {
        return $this->morphMany(Attendance::class, 'attendable');
    }

    /**
     * Get all notifications related to this event.
     */
    public function notifications(): MorphMany
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }

    /**
     * Scope a query to only include events for a specific semester.
     */
    public function scopeForSemester($query, int $semesterId)
    {
        return $query->where('semester_id', $semesterId);
    }

    /**
     * Scope a query to only include events for a specific section.
     */
    public function scopeForSection($query, int $sectionId)
    {
        return $query->where('section_id', $sectionId);
    }

    /**
     * Scope a query to only include events of a specific type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('event_type', $type);
    }

    /**
     * Scope a query to only include events with a specific status.
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include published events.
     */
    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }


    /**
     * Scope a query to filter events by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('start_date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to filter upcoming events.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>=', now()->toDateString())
                    ->where('status', '!=', self::STATUS_CANCELLED)
                    ->where('status', '!=', self::STATUS_COMPLETED);
    }

    /**
     * Scope a query to filter past events.
     */
    public function scopePast($query)
    {
        return $query->where('end_date', '<', now()->toDateString())
                    ->orWhere('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope a query to filter events happening today.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('start_date', now()->toDateString());
    }

    /**
     * Scope a query to only include approved events.
     */
    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_at');
    }

    /**
     * Scope a query to only include events requiring approval.
     */
    public function scopePendingApproval($query)
    {
        return $query->whereNull('approved_at')
                    ->where('status', self::STATUS_PENDING);
    }

    /**
     * Determine if the event is published.
     *
     * @return bool
     */
    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    /**
     * Determine if the event is public.
     *
     * @return bool
     */
    public function isPublic(): bool
    {
        return $this->is_public;
    }

    /**
     * Determine if the event is featured.
     *
     * @return bool
     */
    public function isFeatured(): bool
    {
        return $this->is_featured;
    }

    /**
     * Determine if the event is approved.
     *
     * @return bool
     */
    public function isApproved(): bool
    {
        return !is_null($this->approved_at);
    }

    /**
     * Determine if the event is upcoming.
     *
     * @return bool
     */
    public function isUpcoming(): bool
    {
        return $this->start_date >= now()->toDateString() 
            && $this->status !== self::STATUS_CANCELLED 
            && $this->status !== self::STATUS_COMPLETED;
    }

    /**
     * Determine if the event is past.
     *
     * @return bool
     */
    public function isPast(): bool
    {
        return $this->end_date < now()->toDateString() || $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Calculate event duration in minutes.
     *
     * @return int|null
     */
    public function calculateDuration(): ?int
    {
        if ($this->start_time && $this->end_time) {
            return $this->start_time->diffInMinutes($this->end_time);
        }

        if ($this->start_date && $this->end_date) {
            return \Carbon\Carbon::parse($this->start_date)->diffInDays(\Carbon\Carbon::parse($this->end_date)) * 1440; // Convert days to minutes
        }

        return null;
    }

    /**
     * Approve the event.
     *
     * @param int $approvedBy
     * @return bool
     */
    public function approve(int $approvedBy): bool
    {
        return $this->update([
            'approved_by' => $approvedBy,
            'approved_at' => now(),
            'status' => self::STATUS_APPROVED,
        ]);
    }

    /**
     * Publish the event.
     *
     * @return bool
     */
    public function publish(): bool
    {
        return $this->update([
            'status' => self::STATUS_PUBLISHED,
        ]);
    }

    /**
     * Cancel the event.
     *
     * @return bool
     */
    public function cancel(): bool
    {
        return $this->update([
            'status' => self::STATUS_CANCELLED,
        ]);
    }

    /**
     * Mark event as completed.
     *
     * @return bool
     */
    public function complete(): bool
    {
        return $this->update([
            'status' => self::STATUS_COMPLETED,
        ]);
    }

    /**
     * Increment participant count.
     *
     * @return bool
     */
    public function incrementParticipants(): bool
    {
        if ($this->hasAvailableSpots()) {
            return $this->increment('current_participants');
        }

        return false;
    }

    /**
     * Decrement participant count.
     *
     * @return bool
     */
    public function decrementParticipants(): bool
    {
        if ($this->current_participants > 0) {
            return $this->decrement('current_participants');
        }

        return false;
    }

    /**
     * Get all available event types.
     *
     * @return array
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_ACADEMIC,
            self::TYPE_SOCIAL,
            self::TYPE_SPORTS,
            self::TYPE_CULTURAL,
            self::TYPE_WORKSHOP,
            self::TYPE_SEMINAR,
            self::TYPE_CONFERENCE,
            self::TYPE_CEREMONY,
            self::TYPE_MEETING,
            self::TYPE_OTHER,
        ];
    }

    /**
     * Get all available event statuses.
     *
     * @return array
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_PENDING,
            self::STATUS_APPROVED,
            self::STATUS_PUBLISHED,
            self::STATUS_CANCELLED,
            self::STATUS_COMPLETED,
            self::STATUS_POSTPONED,
        ];
    }

}

