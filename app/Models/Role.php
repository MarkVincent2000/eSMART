<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    /**
     * Allow mass assignment of all attributes so the package
     * can freely manage its own columns.
     */
    protected $guarded = [];
}


