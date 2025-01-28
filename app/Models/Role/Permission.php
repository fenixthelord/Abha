<?php

namespace App\Models\Role;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission as BasePermission;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditingTrait;
use App\Http\Traits\HasAutoPermissions;
use Spatie\Translatable\HasTranslations;

class Permission extends BasePermission  implements Auditable
{
    use AuditingTrait;
    use \OwenIt\Auditing\Auditable;
    //use HasAutoPermissions;
    // You may add additional properties or methods here
    use HasTranslations;

    private $translatable = ['displaying'];
}
