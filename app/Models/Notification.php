<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;
use App\Http\Traits\HasDateTimeFields;

class Notification extends BaseModel   implements Auditable
{
    use HasFactory, HasDateTimeFields;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'notifications';
    protected $fillable = [
        'sender_id',
        'title',
        'description',
        'image',
        'url',
        'schedule_at',
    ];    protected $casts = [
        'sender_id' => 'string',
        'title' => 'string',
        'description' => 'string',
        'image' => 'string',
        'url' => 'string',
    ];
    public function recipients(): HasMany
    {
        return $this->hasMany(NotificationDetail::class);
    }
}
