<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    /**
     * Allow mass assignment of all attributes so the package
     * can freely manage its own columns.
     */
    protected $guarded = [];
}


