<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\LoggerTrait;

class UserPersonalDetails extends Model
{
    use HasFactory, LoggerTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'guardian_first_name',
        'guardian_last_name',
        'guardian_middle_name',
        'guardian_suffix',
        'guardian_relationship',
        'guardian_contact_no',
        'sex',
        'address',
        'contact_no',
        'date_of_birth',
        'religion',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date_of_birth' => 'date',
    ];

    /**
     * Get the user that owns these personal details.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}


