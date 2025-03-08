<?php

namespace App\Models;

use App\Http\Traits\HasDateTimeFields;
use OwenIt\Auditing\Auditable as AuditingTrait;
use OwenIt\Auditing\Contracts\Auditable;

class Audit extends BaseModel  implements Auditable
{
    use HasDateTimeFields;
    use AuditingTrait;
    use \OwenIt\Auditing\Auditable;
    protected $fillable = [
        'id',
        'user_id',
        'user_type',
        'user_full_name',
        'event',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'url',
        'ip_address',
        'user_agent',
        'tags',

    ];

    public function auditable()
    {
        return $this->morphTo();
    }
}
