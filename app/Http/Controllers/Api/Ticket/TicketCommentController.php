<?php

namespace App\Http\Controllers\Api\Ticket;

use App\Http\Controllers\Controller;
use App\Http\Traits\Paginate;
use App\Http\Traits\ResponseTrait;
use App\Models\CommentMention;
use App\Http\Requests\Ticket\StoreTicketCommentRequest;
use App\Http\Requests\Ticket\UpdateTicketCommentRequest;
use Illuminate\Http\Request;
use App\Models\TicketComment;

class TicketCommentController extends Controller
{
    use ResponseTrait, Paginate;
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(StoreTicketCommentRequest $request)
    {
        $user = auth()->user()?->id;
        $request['user_id'] = $user;
        $validatedData = $request->validated();

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


    public function update(UpdateTicketCommentRequest $request)
    {
        $id = $request->input('id');
        $comment = TicketComment::findOrFail($id);
        if ($comment->user_id != $request->user()->id) {
            return $this->badRequest('You cannot edit this comment.');
        }
        $comment->update($request->only('content'));
        $comment->mentions()->delete();
        $comment->parseMentions();
        return $this->returnData($comment,'comment updated');
    }
}
