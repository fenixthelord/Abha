<?php

namespace App\Models;

use App\Http\Traits\HasAutoPermissions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NotifyGroup extends Model
{
    use HasFactory;
    use HasAutoPermissions;
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
        return $this->belongsToMany(User::class, 'notify_group_user');
    }

}
