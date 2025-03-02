<?php

namespace App\Http\Controllers\Api\Ticket;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ticket\StoreTicketCommentRequest;
use App\Http\Requests\Ticket\UpdateTicketCommentRequest;
use Illuminate\Http\Request;
use App\Models\TicketComment;
use App\Http\Traits\ResponseTrait;

class TicketCommentController extends Controller
{
    use ResponseTrait;
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(StoreTicketCommentRequest $request)
    {
        $validatedData = $request->validated();
        $validatedData['user_id'] = $request->user()->id;
        $comment = TicketComment::create($validatedData);
        $comment->parseMentions();
        return $this->returnData($comment,'comment added');
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
