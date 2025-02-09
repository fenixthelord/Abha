<?php

namespace App\Models;

use App\Http\Traits\HasAutoPermissions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Translatable\HasTranslations;

class NotifyGroup extends BaseModel   implements Auditable
{
    use HasFactory;
    use HasAutoPermissions, HasTranslations;
    use \OwenIt\Auditing\Auditable;

    private $translatable = ['name', 'description'];
    protected $fillable = ['name', 'description', 'model'];
    protected $casts = [
        'name' => 'string',
        'description' => 'string',
        'model' => 'string',
        ];

    public function getTransAble()
    {
        return ['name', 'description']; // Example columns for translation
    }

    // Relationship with users
    public function users()
    {
        return $this->belongsToMany(User::class, 'notify_group_user', 'notify_group_id', 'user_id', 'id', 'id');
    }
}
