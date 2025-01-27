<?php

namespace App\Models;

use App\Http\Traits\HasAutoPermissions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;

class NotifyGroup extends Model   implements Auditable
{
    use HasFactory;
    use HasAutoPermissions;
    use \OwenIt\Auditing\Auditable;
    protected $fillable = ['uuid', 'name', 'description','model'];

    // Automatically generate UUID when creating a new NotifyGroup
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = Str::uuid();
        });
    }

    // Relationship with users
    public function users()
    {
        return $this->belongsToMany(User::class, 'notify_group_user', 'notify_group_uuid', 'user_uuid', 'uuid', 'uuid');
    }

}
