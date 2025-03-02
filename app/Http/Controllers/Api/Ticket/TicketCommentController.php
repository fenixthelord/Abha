<?php

namespace App\Http\Controllers\Api\Ticket;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TicketComment;

class TicketCommentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request)
    {
        $request->validate([
            'ticket_id' => 'required|exists:tickets,id',
            'user_id' => 'required|exists:users,id',
            'content' => 'required|string',
        ]);

        $comment = TicketComment::create($request->all());
        $comment->parseMentions();

        return response()->json(['message' => 'Comment added', 'comment' => $comment], 201);
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
