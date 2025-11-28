<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

use App\Traits\LoggerTrait;
class Role extends SpatieRole
{
    use LoggerTrait;
    /**
     * Allow mass assignment of all attributes so the package
     * can freely manage its own columns.
     */
    protected $guarded = [];
}


