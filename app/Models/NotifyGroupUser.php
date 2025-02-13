<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotifyGroupUser extends Model
{
    use HasFactory;

    protected $table = 'notify_group_user';

    protected $fillable = [
        'notify_group_uuid ',
        'user_uuid',
    ];
}
