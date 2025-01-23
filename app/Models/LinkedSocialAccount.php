<?php

namespace App\Models;

use App\Http\Traits\HasAutoPermissions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class LinkedSocialAccount extends Model   implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    use HasAutoPermissions;
    protected $fillable = [
        'provider_name',
        'provider_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
