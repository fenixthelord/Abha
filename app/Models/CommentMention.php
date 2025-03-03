<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CommentMention extends BaseModel
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['id', 'comment_id', 'type', 'identifier', 'type_id'];


    public function comment()
    {
        return $this->belongsTo(TicketComment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
