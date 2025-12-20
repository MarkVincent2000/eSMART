<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LoggerTrait;
use App\Models\CommentReply;

class Comment extends Model
{
    use HasFactory, SoftDeletes, LoggerTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'comments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'commentable_type',
        'commentable_id',
        'user_id',
        'body',
        'parent_id',
        'status',
        'metadata',
        'ip_address',
        'user_agent',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Status constants.
     */
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_PENDING = 'pending';
    public const STATUS_DELETED = 'deleted';
    public const STATUS_SPAM = 'spam';

    /**
     * Get all available comment statuses.
     *
     * @return array
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PUBLISHED,
            self::STATUS_PENDING,
            self::STATUS_DELETED,
            self::STATUS_SPAM,
        ];
    }

    /**
     * Get the model that this comment belongs to (polymorphic).
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who created this comment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent comment (if this is a reply).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * Get all direct replies to this comment.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id')
            ->where('status', self::STATUS_PUBLISHED)
            ->orderBy('created_at', 'asc');
    }

    /**
     * Get all replies (including nested) via pivot table.
     */
    public function allReplies(): BelongsToMany
    {
        return $this->belongsToMany(Comment::class, 'comment_replies', 'comment_id', 'reply_id')
            ->using(CommentReply::class)
            ->withPivot('depth')
            ->withTimestamps()
            ->orderByPivot('created_at', 'asc');
    }

    /**
     * Get comments that this comment replies to.
     */
    public function repliedTo(): BelongsToMany
    {
        return $this->belongsToMany(Comment::class, 'comment_replies', 'reply_id', 'comment_id')
            ->using(CommentReply::class)
            ->withPivot('depth')
            ->withTimestamps();
    }

    /**
     * Get all attachments for this comment.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(CommentAttachment::class)
            ->orderBy('display_order', 'asc')
            ->orderBy('created_at', 'asc');
    }

    /**
     * Check if this comment is a reply.
     *
     * @return bool
     */
    public function isReply(): bool
    {
        return !is_null($this->parent_id);
    }

    /**
     * Check if this comment has replies.
     *
     * @return bool
     */
    public function hasReplies(): bool
    {
        return $this->replies()->exists();
    }

    /**
     * Scope a query to only include published comments.
     */
    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    /**
     * Scope a query to only include top-level comments (not replies).
     */
    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope a query to only include replies.
     */
    public function scopeReplies($query)
    {
        return $query->whereNotNull('parent_id');
    }

    /**
     * Scope a query to only include comments for a specific model.
     */
    public function scopeForModel($query, $model)
    {
        return $query->where('commentable_type', get_class($model))
            ->where('commentable_id', $model->id);
    }
}
