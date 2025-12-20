<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * CommentReply Pivot Model
 * 
 * This model represents the many-to-many relationship between
 * Comments and their replies, allowing for nested comment structures.
 */
class CommentReply extends Pivot
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'comment_replies';

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
        'comment_id',
        'reply_id',
        'depth',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'comment_id' => 'integer',
        'reply_id' => 'integer',
        'depth' => 'integer',
    ];

    /**
     * Get the parent comment that this reply belongs to.
     */
    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'comment_id');
    }

    /**
     * Get the reply comment.
     */
    public function reply(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'reply_id');
    }
}
