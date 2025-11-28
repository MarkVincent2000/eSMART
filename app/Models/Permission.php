<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

use App\Traits\LoggerTrait;

class Permission extends SpatiePermission
{
    use LoggerTrait;
    /**
     * Allow mass assignment of all attributes so the package
     * can freely manage its own columns.
     */
    protected $guarded = [];
}


