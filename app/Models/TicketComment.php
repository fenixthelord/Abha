<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Department;
use App\Models\Position;
use App\Models\CommentMention;

class TicketComment extends BaseModel
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['id', 'ticket_id', 'user_id', 'content'];

    protected $casts = [
        'mentions' => 'array',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function mentions()
    {
        return $this->hasMany(CommentMention::class, 'comment_id');
    }

    public function parseMentions()
    {
        preg_match_all('/(@[a-zA-Z0-9_]+|#[a-zA-Z0-9_]+|![a-zA-Z0-9_]+)/', $this->content, $matches);
        $mentionsData = [];

        foreach ($matches[0] as $mention) {
            if (Str::startsWith($mention, '@')) {
                $username = Str::replaceFirst('@', '', $mention);
                $user = User::where('first_name', 'LIKE', "%$username%")
                    ->orWhere('last_name', 'LIKE', "%$username%")
                    ->first();
                if ($user) {
                    CommentMention::create([
                        'comment_id' => $this->id,
                        'type' => 'user',
                        'identifier' => $username,
                        'type_id' => $user->id
                    ]);
                    $mentionsData[] = ['type' => 'user', 'identifier' => $username];
                }
            } elseif (Str::startsWith($mention, '#')) {
                $departmentName = Str::replaceFirst('#', '', $mention);
                $department = Department::where('name', 'LIKE', "%$departmentName%")->first();
                if ($department) {
                    foreach ($department->employees as $user) {
                        CommentMention::create([
                            'comment_id' => $this->id,
                            'type' => 'department',
                            'identifier' => $departmentName,
                            'type_id' => $user->id
                        ]);
                    }
                    $mentionsData[] = ['type' => 'department', 'identifier' => $departmentName];
                }
            } elseif (Str::startsWith($mention, '!')) {
                $positionName = Str::replaceFirst('!', '', $mention);
                $position = Position::where('name', 'LIKE', "%$positionName%")->first();
                if ($position) {
                    foreach ($position->users as $user) {
                        CommentMention::create([
                            'comment_id' => $this->id,
                            'type' => 'position',
                            'identifier' => $positionName,
                            'type_id' => $user->id
                        ]);
                    }
                    $mentionsData[] = ['type' => 'position', 'identifier' => $positionName];
                }
            }
        }

        $this->mentions = $mentionsData;
        $this->save();
    }
}
