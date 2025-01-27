<?php

namespace App\Models\Role;

use App\Http\Traits\HasAutoPermissions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role as BaseRole;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditingTrait;

class Role extends BaseRole   implements Auditable
{
    use AuditingTrait;
    use \OwenIt\Auditing\Auditable;
    //use HasAutoPermissions;

    // You may add additional properties or methods here
}
