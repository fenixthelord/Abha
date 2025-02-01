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
                'group_uuid' => 'nullable|exists:notify_groups,uuid',
                'user_uuids' => 'nullable|array',
                //'user_uuids.*' => 'exists:users,uuid',
            ]);

            // Fetch device tokens
            $tokens = [];
            $content = [
                'title' => $request->input('title'),
                'body' => $request->input('body'),
                'image' => $request->input('image'),
                'data' => $request->input('data', []), // Additional metadata
            ];

            if ($request->has('group_uuid')) {
                // Fetch tokens for all users in the group
                $group = NotifyGroup::where('uuid', $request->input('group_uuid'))->firstOrFail();
                $userIds = $group->users()->pluck('users.id');
                $tokens = DeviceToken::whereIn('user_id', $userIds)->pluck('token')->toArray();
            } elseif ($request->has('user_uuids')) {
                if ($request->user_uuids[0] == '*') {
                    $this->store($request);
                    User::chunk(100, function ($users) use ($content) {
                        foreach ($users as $user) {
                            $tokens = $user->deviceTokens()->pluck('token');
                            $status = $this->HandelDataAndSendNotify($tokens, $content);
                        }
                    });
                    return $status
                        ? $this->returnSuccessMessage('Notifications sent successfully!')
                        : $this->returnError('Failed to send notifications.');
                } else {
                    $userIds = User::whereIn('uuid', $request->input('user_uuids'))->pluck('id');
                }

                $tokens = DeviceToken::whereIn('user_id', $userIds)->pluck('token')->toArray();
            }
            $this->store($request);

            if (empty($tokens)) {
                return $this->badRequest('No device tokens found for the specified users or group.');
            }
            $status = $this->HandelDataAndSendNotify($tokens, $content);

            DB::commit();

            return $status
                ? $this->returnSuccessMessage('Notifications sent successfully!')
                : $this->returnError('Failed to send notifications.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }

    public function saveDeviceToken(Request $request)
    {
        DB::beginTransaction();
        $request->validate([
            'token' => 'required|string',
            'user_uuid' => 'nullable|exists:users,uuid',
        ]);

        try {
            $user = User::where('uuid', $request->input('user_uuid'))->first();
            DeviceToken::firstOrCreate([
                'token' => $request->input('token'),
                'user_id' => $user->id,
            ]);
            DB::commit();
            return $this->returnSuccessMessage('Device Token saved successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }

    public function allNotification(Request $request)
    {
        try {
            $pageNumber = $request->input('page', 1);
            $perPage = $request->input("perPage", 10);

/*            $validator = Validator::make($request->all(), [
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
            $fields = ['title','description'];
            $group = $this->allWithSearch(new Notification(), $fields, $pageNumber, $perPage);
            return $this->returnData($data);
        } catch (\Exception $e) {
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
        if ($request->has('group_uuid')) {
            if (NotifyGroup::whereuuid($request->group_uuid)) {
                $notification->recipients()->create([
                    'recipient_type' => 'group',
                    'recipient_uuid' => $request->group_uuid,
                ]);
            }
        }
        if ($request['user_uuids']) {
            $recipient = $request['user_uuids'];
            if ($recipient[0] == '*') {
                $notification->for_all = true;
                $notification->save();
            } else {
                foreach ($request['user_uuids'] as $recipient) {
                    if (User::whereuuid($recipient)) {
                        $notification->recipients()->create([
                            'recipient_type' => 'user',
                            'recipient_uuid' => $recipient
                        ]);
                    }
                }
            }
        }

        return $this->returnSuccessMessage('Notification sent successfully.');
    }

    private function processRecipient(Notification $notification, string $recipient)
    {
        if ($recipient == '*') {
            $notification->for_all = true;
            $notification->save();
            return true;
        }


        [$type, $uuid] = explode(':', $recipient);

        // التحقق من صحة الـ ID
        $model = match ($type) {
            'user' => \App\Models\User::class,
            'group' => \App\Models\NotifyGroup::class,
            default => null
        };

        if (!$model || !$model::where('uuid', $uuid)->exists()) {
            return $this->badRequest('Recipient not found.');
        }

        $notification->recipients()->create([
            'recipient_type' => $type,
            'recipient_uuid' => $uuid
        ]);
    }

    public function getUserNotifications(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'perPage' => 'nullable|integer|min:9',
                'uuid' => 'required|exists:users,uuid',
            ]);
            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }
            $pageNumber = request()->input('page', 1);
            $perPage = request()->input('perPage', 10);
            if ($user = User::where('uuid', $request->uuid)->first()) {
                $notifications = Notification::where('for_all', true)
                    ->orwhereHas('recipients', function ($query) use ($user) {
                        $query->where(function ($q) use ($user) {
                            $q->where('recipient_type', 'user')
                                ->where('recipient_uuid', $user->uuid);
                        })
                            ->orWhere(function ($q2) use ($user) {
                                $q2->where('recipient_type', 'group')
                                    ->whereIn('recipient_uuid', $user->groups()->pluck('uuid'));
                            });
                    })->with('recipients')->paginate($perPage, ['*'], 'page', $pageNumber);
                if ($pageNumber > $notifications->lastPage() || $pageNumber < 1 || $perPage < 1) {
                    return $this->badRequest('Invalid page number');
                }
                $data['users'] = NotificationResource::collection($notifications);
                return $this->PaginateData($data , $notifications );
            } else {
                return $this->badRequest('user not found.');
            }
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
