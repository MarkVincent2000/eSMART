<?php

namespace App\Models;

    use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LoggerTrait;

class CommentAttachment extends Model
{
    use HasFactory, SoftDeletes, LoggerTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'comment_attachments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'comment_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'original_name',
        'metadata',
        'display_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'file_size' => 'integer',
        'display_order' => 'integer',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the comment that this attachment belongs to.
     */
    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }

    /**
     * Get the full URL to the attachment file.
     *
     * @return string
     */
    public function getUrlAttribute(): string
    {
        // If it's an external URL, return as-is
        if (filter_var($this->file_path, FILTER_VALIDATE_URL)) {
            return $this->file_path;
        }
        
        // Otherwise, prepend storage path
        return asset('storage/' . $this->file_path);
    }

    /**
     * Check if the attachment is an image.
     *
     * @return bool
     */
    public function isImage(): bool
    {
        return str_starts_with($this->file_type ?? '', 'image/');
    }

    /**
     * Check if the attachment is a document.
     *
     * @return bool
     */
    public function isDocument(): bool
    {
        $documentTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];
        
        return in_array($this->file_type, $documentTypes);
    }

    /**
     * Get human-readable file size.
     *
     * @return string
     */
    public function getHumanReadableSizeAttribute(): string
    {
        if (!$this->file_size) {
            return 'Unknown';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }
}
