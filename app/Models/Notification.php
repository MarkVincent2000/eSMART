<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'url',
        'body',
        'data',
        'read_at',
        'notifiable_id',
        'notifiable_type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    /**
     * Get the user that owns the notification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent notifiable model (polymorphic relation).
     */
    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Mark the notification as read.
     *
     * @return bool
     */
    public function markAsRead(): bool
    {
        if (is_null($this->read_at)) {
            return $this->update(['read_at' => now()]);
        }

        return false;
    }

    /**
     * Mark the notification as unread.
     *
     * @return bool
     */
    public function markAsUnread(): bool
    {
        if (!is_null($this->read_at)) {
            return $this->update(['read_at' => null]);
        }

        return false;
    }

    /**
     * Determine if the notification has been read.
     *
     * @return bool
     */
    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }

    /**
     * Determine if the notification has not been read.
     *
     * @return bool
     */
    public function isUnread(): bool
    {
        return is_null($this->read_at);
    }

    /**
     * Create a notification for a student when their grade is saved.
     *
     * @param int $userId The student's user ID (recipient of the notification).
     * @param \App\Models\Grading\StudentInfoGrade $studentInfoGrade The grade record that was saved.
     * @param string|null $teacherName Name of the teacher who saved the grade.
     * @return self
     */
    public static function notifyStudentGradeSaved(int $userId, \App\Models\Grading\StudentInfoGrade $studentInfoGrade, ?string $teacherName = null): self
    {
        $teacherName = $teacherName ?? $studentInfoGrade->teacher_name ?? 'Your teacher';
        $title = 'Grade record updated';
        $body = sprintf(
            '%s has saved your grade record for School Year %s (Grade %s). You can view your grades in the grading section.',
            $teacherName,
            $studentInfoGrade->school_year ?? 'N/A',
            $studentInfoGrade->grade ?? 'N/A'
        );

        return self::create([
            'user_id' => $userId,
            'type' => 'grade_saved',
            'title' => $title,
            'body' => $body,
            'url' => '/teacher.index-grade',
            'data' => [
                'student_info_grade_id' => $studentInfoGrade->id,
                'school_year' => $studentInfoGrade->school_year,
                'grade' => $studentInfoGrade->grade,
                'teacher_name' => $teacherName,
            ],
            'notifiable_id' => $studentInfoGrade->id,
            'notifiable_type' => \App\Models\Grading\StudentInfoGrade::class,
        ]);
    }
}

