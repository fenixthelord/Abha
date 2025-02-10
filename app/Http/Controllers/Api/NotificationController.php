<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Http\Traits\Firebase;
use App\Http\Traits\ResponseTrait;
use App\Models\Notification;
use App\Models\NotifyGroup;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\DeviceToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Traits\Paginate;

class NotificationController extends Controller
{
    use Firebase, ResponseTrait, Paginate;
    public function sendNotification(Request $request)
    {
        global $status;
        DB::beginTransaction();

        try {
            // Validate request
            $request->validate([
                'title' => 'required|string',
                'body' => 'required|string',
                'image' => 'nullable|string',
                'data' => 'nullable|array',
                'group_id' => 'nullable|exists:notify_groups,id',
                'user_ids' => 'nullable|array',

                //'user_ids.*' => 'exists:users,id',
            ]);

            // Fetch device tokens
            $tokens = [];
            $content = [
                'title' => $request->input('title'),
                'body' => $request->input('body'),
                'image' => $request->input('image'),
                'data' => $request->input('data', []), // Additional metadata
            ];

            if ($request->has('group_id')) {
                // Fetch tokens for all users in the group
                $group = NotifyGroup::whereId('id', $request->input('group_id'))->firstOrFail();
                $userIds = $group->users()->pluck('users.id');
                $tokens = DeviceToken::whereIn('user_id', $userIds)->pluck('token')->toArray();
            } elseif ($request->has('user_ids')) {
                if ($request->user_ids[0] == '*') {
                    $this->store($request);
                    User::chunk(100, function ($users) use ($content) {
                        foreach ($users as $user) {
                            $tokens = $user->deviceTokens()->pluck('token');
                            $status = $this->HandelDataAndSendNotify($tokens, $content);
                        }
                    });
                    return $status
                        ? $this->returnSuccessMessage(__('validation.custom.notification.notification_sent_success'))
                        : $this->returnError(__('validation.custom.notification.notification_sent_fail'));
                } else {
                    $userIds = User::whereIn('id', $request->input('user_ids'))->pluck('id');
                }

                $tokens = DeviceToken::whereIn('user_id', $userIds)->pluck('token')->toArray();
            }
            $this->store($request);

            if (empty($tokens)) {
                return $this->badRequest(__('validation.custom.notification.no_device_tokens'));
            }
            $status = $this->HandelDataAndSendNotify($tokens, $content);

            DB::commit();

            return $status
                ? $this->returnSuccessMessage(__('validation.custom.notification.notification_sent_success'))
                : $this->returnError(__('validation.custom.notification.notification_sent_fail'));
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }





    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'image' => 'nullable|string',
            'url' => 'nullable|string',
            'schedule_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return $this->returnValidationError($validator);
        }
        $data = $validator->validated();
        /*if (in_array('*', $data['recipients']) && count($data['recipients']) > 1) {
            return $this->badRequest('You cannot send notifications to one and more recipients.');
        }*/
        $notification = Notification::create([
            'title' => $request['title'],
            'description' => $request['body'],
            'image' => $request['image'] ?? null,
            'url' => $request['url'] ?? null,
            'scheduled_at' => $request['scheduled_at'] ?? null,
            'sender_id' => $request->user()->id,
        ]);
        if ($request->has('group_id')) {
            if (NotifyGroup::whereId($request->group_id)) {
                $notification->recipients()->create([
                    'recipient_type' => 'group',
                    'recipient_id' => $request->group_id,
                ]);
            }
        }
        if ($request['user_ids']) {
            $recipient = $request['user_ids'];
            if ($recipient[0] == '*') {
                $notification->for_all = true;
                $notification->save();
            } else {
                foreach ($request['user_ids'] as $recipient) {
                    if (User::whereId($recipient)) {
                        $notification->recipients()->create([
                            'recipient_type' => 'user',
                            'recipient_id' => $recipient
                        ]);
                    }
                }
            }
        }

        return $this->returnSuccessMessage(__('validation.custom.notification.notification_sent_success'));
    }

    private function processRecipient(Notification $notification, string $recipient)
    {
        if ($recipient == '*') {
            $notification->for_all = true;
            $notification->save();
            return true;
        }
        [$type, $id] = explode(':', $recipient);
        // التحقق من صحة الـ ID
        $model = match ($type) {
            'user' => \App\Models\User::class,
            'group' => \App\Models\NotifyGroup::class,
            default => null
        };

        if (!$model || !$model::whereId($id)->exists()) {
            return $this->badRequest('Recipient not found.'); //??
        }

        $notification->recipients()->create([
            'recipient_type' => $type,
            'recipient_id' => $id
        ]);
    }
    public function getUserNotifications(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'perPage' => 'nullable|integer|min:9',
                'id' => 'required|exists:users,id',
            ]);
            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }
            $pageNumber = request()->input('page', 1);
            $perPage = request()->input('perPage', 10);
            if ($user = User::whereId($request->id)->first()) {
                $notifications = Notification::where('for_all', true)
                    ->orwhereHas('recipients', function ($query) use ($user) {
                        $query->where(function ($q) use ($user) {
                            $q->where('recipient_type', 'user')
                                ->where('recipient_id', $user->id);
                        })
                            ->orWhere(function ($q2) use ($user) {
                                $q2->where('recipient_type', 'group')
                                    ->whereIn('recipient_id', $user->groups()->pluck('id'));
                            });
                    })->with('recipients')->paginate($perPage, ['*'], 'page', $pageNumber);
                if ($pageNumber > $notifications->lastPage() || $pageNumber < 1 || $perPage < 1) {
                    return $this->badRequest('Invalid page number');
                }
                $data['users'] = NotificationResource::collection($notifications);
                return $this->PaginateData($data, $notifications);
            } else {
                return $this->badRequest(__('validation.custom.notification.user_not_found'));
            }
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
    public function allNotification(Request $request)
    {
        try {
            /*            $pageNumber = $request->input('page', 1);
            $perPage = $request->input("perPage", 10);

            $validator = Validator::make($request->all(), [
                "perPage" => 'nullable|integer|min:9'
            ]);
            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }
            $customer = $request->user();
            $notifications = Notification::paginate($perPage, ['*'], 'page', $pageNumber);
            if ($pageNumber > $notifications->lastPage()) {
                return $this->badRequest('Invalid page number');
            }

            $data = [
                "notification" => NotificationResource::collection($notifications),
                'current_page' => $notifications->currentPage(),
                'next_page' => $notifications->nextPageUrl(),
                'previous_page' => $notifications->previousPageUrl(),
                'total_pages' => $notifications->lastPage(),
            ];*/
            $fields = ['title', 'description'];
            $notification = $this->allWithSearch(new Notification(), $fields, $request);
            $data['notification'] = NotificationResource::collection($notification);
            return $this->PaginateData($data, $notification);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
