<?php

namespace App\Http\Controllers\Api\Notification;

use App\Http\Controllers\Controller;
use App\Http\Requests\Notification\SendNotificationRequest;
use App\Http\Traits\ResponseTrait;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ResponseTrait;
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function sendNotification(SendNotificationRequest $request)
    {
        try {
            $userAuth=auth('sanctum')->user();
            // Validate and retrieve request data
            $data = $request->validated();

            // Retrieve all users in one query for better performance
            $userIds = $data['user_ids']??null;
            $users = User::whereIn('id', $userIds)->get()->keyBy('id');

            $userIds = $data['user_ids'];

            // Process the user_ids:
            // - If the first element is "*", set the new array with wildcard values.
            // - Otherwise, retrieve users in one query and map each user_id to include department_id.
            $newUserIds = collect($userIds)->when(
                isset($userIds[0]) && $userIds[0] === '*',
                function ($collection) {
                    return collect(['user_id' => '*']);
                },
                function ($collection) {
                    $users = \App\Models\User::whereIn('id', $collection->all())
                        ->get()
                        ->keyBy('id');
                    return $collection->map(function ($userId) use ($users) {
                        return [
                            'user_id'       => $userId,
                            'department_id' => $users->has($userId) ? $users[$userId]->department_id : null,
                        ];
                    });
                }
            )->toArray();
          $data['user_ids'] = $newUserIds;

            // Prepare the notification data
            $notificationData = [
                'sender_id'        => $userAuth->id,
                'sender_type'      => $data['sender_type'] ?? 'user',
                'sender_service'   => $data['sender_service'] ?? 'user_service', // Default value if not provided
                'title'            => $data['title'],
                'body'             => $data['body'],
                'user_ids'         => $data['user_ids'], // Use the transformed user_ids array
                'receiver_service' => $data['model']=='user' ? 'user_service':'customer_service',
                'receiver_type'    => $data['model'] ?? 'user',
                'group_id'         => $data['group_id'] ?? null,
                'channel'          => $data['channel'] ?? 'fcm',
                'image'            => $data['image'] ?? null,
                'url'              => $data['url'] ?? null,
           // Use current time if not provided

            ];
//dd($notificationData);
            // Send the notification using the NotificationService
            $response = $this->notificationService->postCall('/send-notification', $notificationData);

            // Return an error response if one exists in the service response
            if (isset($response['error'])) {
                return $this->returnError($response['error']);
            }

            // Return a success response if the notification was sent successfully
            return $this->returnSuccessMessage('Notification sent successfully');
        } catch (\Exception $e) {
            // Handle any exceptions with a custom response using the handleException method
            return $this->handleException($e);
        }
    }

}
