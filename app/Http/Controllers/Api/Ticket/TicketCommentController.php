<?php

namespace App\Http\Controllers\Api\Ticket;

use App\Http\Controllers\Controller;
use App\Http\Traits\Paginate;
use App\Http\Traits\ResponseTrait;
use App\Models\CommentMention;
use App\Http\Requests\Ticket\StoreTicketCommentRequest;
use App\Http\Requests\Ticket\UpdateTicketCommentRequest;
use App\Models\Ticket;
use App\Services\UserNotificationService;
use App\Models\TicketComment;

class TicketCommentController extends Controller
{
    use ResponseTrait, Paginate;
    protected $userNotificationService;


    public function __construct(UserNotificationService $userNotificationService)
    {
        $this->userNotificationService = $userNotificationService;
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
                    'type'       => $mention['type'],
                    'identifier' => $mention['identifier'],
                    'user_id'    => $mention['id'] ?? null,
                ]);

                // تجميع الـ ids حسب النوع
                if ($mention['type'] === 'department' && !empty($mention['id'])) {
                    $departmentIds[] = $mention['id'];
                } elseif ($mention['type'] === 'position' && !empty($mention['id'])) {
                    $positionIds[] = $mention['id'];
                } elseif ($mention['type'] === 'user' && !empty($mention['id'])) {
                    $userIds[] = $mention['id'];
                }
            }
        }
        $ticket = Ticket::find($validatedData['ticket_id']);
        $notificationData = [
            'title'       => 'New Comment on Ticket: ' . $ticket->name,
            'body'        => $validatedData['content'],
            'object_data' => [
                'id'   => $ticket->id,
                'name' => $ticket->name,
            ],
        ];
        if (!empty($departmentIds)) {
            $notificationData['department_ids'] = array_unique($departmentIds);
        }
        if (!empty($positionIds)) {
            $notificationData['position_ids'] = array_unique($positionIds);
        }
        if (!empty($userIds)) {
            $notificationData['user_ids'] = array_unique($userIds);
        }
        $this->userNotificationService->sendNotification($notificationData, auth()->user());
       return $this->returnData($comment->load('mentions'));
    }


    public function update(UpdateTicketCommentRequest $request)
    {
        $validatedData = $request->validated();
        $comment = TicketComment::findOrFail($validatedData['id']);
        $comment->update(['content' => $validatedData['content']]);
        $id = $request->input('id');
        $comment = TicketComment::findOrFail($id);
        if ($comment->user_id != $request->user()->id) {
            return $this->badRequest('You cannot edit this comment.');
        }
        $comment->update($request->only('content'));
        $comment->mentions()->delete();
        $departmentIds = [];
        $positionIds   = [];
        $userIds       = [];
        if (!empty($validatedData['mentions'])) {
            foreach ($validatedData['mentions'] as $mention) {
                CommentMention::create([
                    'comment_id' => $comment->id,
                    'type'       => $mention['type'],
                    'identifier' => $mention['identifier'],
                    'user_id'    => $mention['id'] ?? null,
                ]);
                if ($mention['type'] === 'department' && !empty($mention['id'])) {
                    $departmentIds[] = $mention['id'];
                } elseif ($mention['type'] === 'position' && !empty($mention['id'])) {
                    $positionIds[] = $mention['id'];
                } elseif ($mention['type'] === 'user' && !empty($mention['id'])) {
                    $userIds[] = $mention['id'];
                }
            }
        } else {
            $comment->parseMentions();
        }
        $ticket = Ticket::find($comment->ticket_id);
        $notificationData = [
            'title'       => 'Comment Updated on Ticket: ' . $ticket->name,
            'body'        => $validatedData['content'],
            'object_data' => [
                'id'   => $ticket->id,
                'name' => $ticket->name,
            ],
        ];
        if (!empty($departmentIds)) {
            $notificationData['department_ids'] = array_unique($departmentIds);
        }
        if (!empty($positionIds)) {
            $notificationData['position_ids'] = array_unique($positionIds);
        }
        if (!empty($userIds)) {
            $notificationData['user_ids'] = array_unique($userIds);
        }
        try {
            $this->userNotificationService->sendNotification($notificationData, auth()->user());
        } catch (Exception $e) {
            return $this->handleException($e);
        }
        return response()->json([
            'message' => 'Comment updated',
            'comment' => $comment->load('mentions')
        ]);
    }

}
