<?php

namespace App\Models\Attendance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LoggerTrait;

class AttendanceCategory extends Model
{
    use HasFactory, LoggerTrait, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'attendance_categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'icon',
        'is_active',
        'display_order',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'display_order' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Get all attendances associated with this category.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'category_id');
    }

    /**
     * Scope a query to only include active categories.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include inactive categories.
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope a query to order categories by display order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order', 'asc');
    }

    /**
     * Scope a query to find a category by slug.
     */
    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }

    /**
     * Determine if the category is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Activate the category.
     *
     * @return bool
     */
    public function activate(): bool
    {
        return $this->update(['is_active' => true]);
    }

    /**
     * Deactivate the category.
     *
     * @return bool
     */
    public function deactivate(): bool
    {
        return $this->update(['is_active' => false]);
    }

    /**
     * Get the count of attendances for this category.
     *
     * @return int
     */
    public function getAttendanceCount(): int
    {
        return $this->attendances()->count();
    }

    /**
     * Get the count of active attendances for this category.
     *
     * @return int
     */
    public function getActiveAttendanceCount(): int
    {
        return $this->attendances()->where('is_active', true)->count();
    }

    /**
     * Get all active categories ordered by display order.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getActiveCategories()
    {
        return self::active()->ordered()->get();
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate slug from name if not provided
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = \Illuminate\Support\Str::slug($category->name);
            }
        });

        // Prevent deletion if category has attendances
        static::deleting(function ($category) {
            if ($category->attendances()->count() > 0) {
                throw new \Exception('Cannot delete category with existing attendances. Please reassign or delete attendances first.');
            }
        });
    }
}
