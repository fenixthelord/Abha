<?php

namespace App\Models\Role;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Spatie\Permission\Models\Role as BaseRole;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditingTrait;
use Spatie\Translatable\HasTranslations;

class Role extends BaseRole implements Auditable
{
    use AuditingTrait;
    use HasTranslations, HasUuids;
    use \OwenIt\Auditing\Auditable;

    protected $primaryKey = 'id';

    private $translatable = ['displaying', 'description'];

    public function getTransAble()
    {
        return ['displaying', 'description'];
    }
}
