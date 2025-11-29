<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LoggerTrait;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes, LoggerTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'middle_name',
        'name_extension',
        'email',
        'password',
        'avatar',
        'photo_path',
        'cover_photo_path',
        'active_status',
    ];

    /**
     * Get the personal details for the user.
     */
    public function personalDetails()
    {
        return $this->hasOne(UserPersonalDetails::class);
    }

    /**
     * Get the primary/default address for the user (from personal details).
     * @deprecated Use personalDetails() instead
     */
    public function primaryAddress()
    {
        return $this->hasOne(UserPersonalDetails::class)->latest();
    }

    /**
     * Get the login histories for the user.
     */
    public function loginHistories()
    {
        return $this->hasMany(LoginHistory::class)->latest('login_at');
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the notifications for the user.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get the student info for the user.
     */
    public function studentInfo()
    {
        return $this->hasOne(\App\Models\StudentDetails\StudentInfo::class);
    }
}
