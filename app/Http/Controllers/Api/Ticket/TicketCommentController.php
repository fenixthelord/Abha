<?php

namespace App\Http\Controllers\Api\Ticket;

use App\Http\Controllers\Controller;
use App\Http\Traits\Paginate;
use App\Http\Traits\ResponseTrait;
use App\Models\CommentMention;
use Illuminate\Http\Request;
use App\Models\TicketComment;

class TicketCommentController extends Controller
{
    use ResponseTrait, Paginate;
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request)
    {
        $user = auth()->user()?->id;
        $request['user_id'] = $user;

        $validatedData = $request->validate([
            'ticket_id' => 'required|exists:tickets,id',
            'user_id' => 'required|exists:users,id',
            'content' => 'required|string',
            'mentions' => 'nullable|array',
            'mentions.*.type' => 'required|string|in:user,department,position',
            'mentions.*.identifier' => 'required|string',
            'mentions.*.id' => 'nullable|string', // Store only if available
        ]);

        // Create Comment
        $comment = TicketComment::create([
            'ticket_id' => $validatedData['ticket_id'],
            'user_id' => $validatedData['user_id'],
            'content' => $validatedData['content'],
        ]);

        // Store Mentions
        if (!empty($validatedData['mentions'])) {
            foreach ($validatedData['mentions'] as $mention) {
                CommentMention::create([
                    'comment_id' => $comment->id,
                    'type' => $mention['type'],
                    'identifier' => $mention['identifier'],
                    'user_id' => $mention['id'] ?? null, // Store user ID if available
                ]);
            }
        }
       return $this->returnData($comment->load('mentions'));

    }


    public function update(Request $request)
    {
        $id = $request->input('id');
        $comment = TicketComment::findOrFail($id);
        $comment->update($request->only('content'));
        $comment->mentions()->delete();
        $comment->parseMentions();

        return response()->json(['message' => 'Comment updated', 'comment' => $comment]);
    }
}
