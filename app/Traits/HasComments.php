<?php

namespace App\Traits;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasComments
{
    /**
     * Get all comments for this model.
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')
            ->whereNull('parent_id') // Only top-level comments
            ->where('status', Comment::STATUS_PUBLISHED)
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get all comments including replies.
     */
    public function allComments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')
            ->where('status', Comment::STATUS_PUBLISHED)
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get all comments including pending and deleted (for moderation).
     */
    public function allCommentsWithModeration(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get the count of published comments.
     */
    public function getCommentsCountAttribute(): int
    {
        return $this->comments()->count();
    }
}

