<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class NotificationDetail extends Model
{
    use HasFactory;
    protected $fillable = ['uuid', 'notification_id', 'recipient_type', 'recipient_uuid'];
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = Str::uuid();
        });
    }
    public function notification(): BelongsTo
    {
        return $this->belongsTo(Notification::class);
    }
}
