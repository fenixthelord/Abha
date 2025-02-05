<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Translatable\HasTranslations;

class Notification extends Model   implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    use HasTranslations;

    protected $table = 'notifications';
    protected $fillable = [
        'uuid',
        'sender_id',
        'title',
        'description',
        'image',
        'url',
        'schedule_at',
    ];
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = Str::uuid();
        });
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
    public function recipients(): HasMany
    {
        return $this->hasMany(NotificationDetail::class);
    }
}
