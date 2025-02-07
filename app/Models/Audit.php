<?php

namespace App\Models;

use OwenIt\Auditing\Auditable;

class Audit extends BaseModel
{
    use Auditable;
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
        'tags'
    ];

    public function auditable()
    {
        return $this->morphTo();
    }
}
