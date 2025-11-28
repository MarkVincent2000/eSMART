<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class RoleHasPermission extends Pivot
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'role_has_permissions';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'permission_id',
        'role_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'permission_id' => 'integer',
        'role_id' => 'integer',
    ];

    /**
     * Get the role that owns this permission assignment.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the permission that is assigned to the role.
     */
    public function permission()
    {
        return $this->belongsTo(Permission::class);
    }
}

