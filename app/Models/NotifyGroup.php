<?php

namespace App\Models;

use App\Http\Traits\HasAutoPermissions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Translatable\HasTranslations;

class NotifyGroup extends Model   implements Auditable
{
    use HasFactory;
    use HasAutoPermissions, HasTranslations;
    use \OwenIt\Auditing\Auditable;

    private $translatable = ['name', 'description'];

    protected $fillable = ['uuid', 'name', 'description', 'model'];
    public function getTransAble()
    {
        return ['name', 'description']; // Example columns for translation
    }

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
