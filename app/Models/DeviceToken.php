<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Contracts\Auditable;

class DeviceToken extends BaseModel implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = ['token', 'user_id'];
    protected $casts = [
        'token' => 'string',
        'user_id' => 'string'];

    // Relationship with user
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
