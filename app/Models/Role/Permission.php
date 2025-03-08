<?php

namespace App\Models\Role;

use Spatie\Permission\Models\Permission as BasePermission;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditingTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Spatie\Translatable\HasTranslations;

class Permission extends BasePermission  implements Auditable
{
    use AuditingTrait;
    use \OwenIt\Auditing\Auditable;

    use HasTranslations, HasUuids;

    protected $primaryKey = 'id';
    private $translatable = ['displaying'];
    public function getTransAble()
    {
        return ['displaying'];
    }
}
