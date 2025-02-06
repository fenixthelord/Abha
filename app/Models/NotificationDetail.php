<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;


class NotificationDetail extends BaseModel   implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['notification_id', 'recipient_type', 'recipient_id'];

    public function notification(): BelongsTo
    {
        return $this->belongsTo(Notification::class);
    }
}
