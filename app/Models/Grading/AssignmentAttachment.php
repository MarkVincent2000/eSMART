<?php

namespace App\Models\Grading;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LoggerTrait;

class AssignmentAttachment extends Model
{
    use HasFactory, SoftDeletes, LoggerTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'assignment_attachments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'assignment_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'original_name',
        'display_order',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'assignment_id' => 'integer',
        'file_size' => 'integer',
        'display_order' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Get the assignment that this attachment belongs to.
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
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
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain',
            'text/csv',
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
            return '0 B';
        }

        $size = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return round($size, 2) . ' ' . $units[$unitIndex];
    }

    /**
     * Get file extension.
     *
     * @return string
     */
    public function getExtensionAttribute(): string
    {
        return pathinfo($this->original_name ?? $this->file_name, PATHINFO_EXTENSION);
    }
}

