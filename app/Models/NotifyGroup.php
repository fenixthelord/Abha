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
    public function deviceTokens()
    {
        return $this->hasManyThrough(
            DeviceToken::class,
            NotifyGroupUser::class,
            'notify_group_uuid',
            'owner_uuid',
            'uuid',
            'user_uuid'
        );
    }

    public function notifyGroupUser()
    {
        return $this->belongsTo(NotifyGroupUser::class, 'owner_uuid', 'user_uuid');
    }



}
