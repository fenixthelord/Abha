<?php

namespace App\Http\Controllers\Api\Notification;

use App\Http\Controllers\Controller;
use App\Http\Requests\Notification\MarkAsDeliveredRequest;
use App\Http\Requests\Notification\SendNotificationRequest;
use App\Http\Resources\Notifications\DetailResource;
use App\Http\Resources\Notifications\NotificationResource;
use App\Http\Traits\ResponseTrait;
use App\Models\User;
use App\Services\NotificationService;
use Exception;
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
            $userAuth = auth('sanctum')->user();
            // Validate and retrieve request data
            $data = $request->validated();

            // Retrieve all users in one query for better performance
            $userIds = $data['user_ids'] ?? null;
            if (isset($userIds)) {
                // Process the user_ids:
                // - If the first element is "*", set the new array with wildcard values.
                // - Otherwise, retrieve users in one query and map each user_id to include department_id.
                $newUserIds = collect($userIds)->when(isset($userIds[0]) && $userIds[0] === '*', function ($collection) {
                    return collect([['user_id' => '*']]); // Ensure correct structure
                }, function ($collection) {
                    $users = User::whereIn('id', $collection->all())->get()->keyBy('id');

                    return $collection->map(function ($userId) use ($users) {
                        return ['user_id' => $userId, 'department_id' => $users->has($userId) ? $users[$userId]->department_id : null,];
                    })->values(); // Reset keys to maintain a clean array structure
                })->toArray();
                $data['user_ids'] = $newUserIds;
            }
            // Prepare the notification data
            $notificationData = ['sender_id' => $userAuth->id,
                'sender_type' => $data['sender_type'] ?? 'user',
                'sender_service' => $data['sender_service'] ?? 'user_service', // Default value if not provided
                'title' => $data['title'],
                'body' => $data['body'],
                'user_ids' => $data['user_ids'] ?? null, // Use the transformed user_ids array
                'receiver_service' => $data['model'] == 'user' ? 'user_service' : 'customer_service',
                'receiver_type' => $data['model'] ?? 'user',
                'group_id' => $data['group_id'] ?? null,
                'channel' => $data['channel'] ?? 'fcm',
                'image' => $data['image'] ?? null,
                'url' => $data['url'] ?? null,// Use current time if not provided

            ];

            // Send the notification using the NotificationService
            $response = $this->notificationService->postCall('/send-notification', $notificationData);
//
            return $response;
            // Return an error response if one exists in the service response
            if (isset($response['error'])) {
                return $this->returnError($response['error']);
            }

            // Return a success response if the notification was sent successfully
            return $this->returnSuccessMessage('Notification sent successfully');
        } catch (Exception $e) {
            // Handle any exceptions with a custom response using the handleException method
            return $this->handleException($e);
        }
    }







    public function callMarkAsDeliveredApi(MarkAsDeliveredRequest $request)
    {
        try {
            // Validate the incoming request
            $validated = $request->validated();

            // Get the current authenticated user ID
            $receiverId = auth('sanctum')->id();

            // Prepare the data to send to the other API
            $data = [
                'receiver_id' => $receiverId,
            ];

            // Check if notification_id is provided
            if ($request->filled('notification_id')) {
                // If notification_id is provided, mark specific notification as delivered
                $data['notification_id'] = $validated['notification_id'];

                // Call the external API to mark this notification as delivered
                $response = $this->notificationService->postCall('/notification/mark-as-delivered', $data);
            } else {
                // If notification_id is not provided, mark all notifications for the user as delivered
                $response = $this->notificationService->postCall('/notification/mark-all-as-delivered', $data);
            }

            // Check for errors in the response
            if (isset($response['error'])) {
                return $this->returnError($response['error']);
            }

            // Return success message
            return $this->returnSuccessMessage('Notification(s) marked as delivered successfully.');
        } catch (Exception $e) {
            // Handle any exceptions
            return $this->handleException($e);
        }
    }



    public function getReceivedNotifications(Request $request) {
        try {
            $user = request()->user('sanctum');

            if (!$user) {
                return $this->Unauthorized(__('validation.custom.notification.user_not_authenticate'));
            }


            $delivered = request()->has('delivered') ? request()->input('delivered') : null;


            $requestData = [
                'receiver_id' => $user->id,
            ];

            if (!is_null($delivered)) {
                $requestData['delivered'] = $delivered;
            }
            $response = $this->notificationService->getCall('/notifications', $requestData);
            $response = json_decode(json_encode($response, true));

            if (isset($response->error)) {
                return $this->returnError($response->error);
            }
            if (!isset($response->data)) {
                return $this->returnError(__('validation.user.invalid_response_data'));
            }

            $response = $response->data;
            // return $response;   
            // $details = collect($response->notifications)->pluck("details")->collapse();
            $notificationsCollection = NotificationResource::collection($response->notifications);

            $data = [
                "notifications" => $notificationsCollection,
                "current_page" => $response->current_page,
                "next_page" => $response->next_page,
                "previous_page" => $response->previous_page,
                "total_pages" => $response->total_pages,
            ];

            return $this->returnData($data);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }


    public function getSentNotifications()
    {
        try {
            $requestData = [
                'sender_id' => Request()->user()->id,
            ];

            $response = $this->notificationService->getCall('/notifications', $requestData);
            $response = json_decode(json_encode($response, true));
            // dd($response->data->current_page, 123);
            if (isset($response->error)) {
                return $this->returnError($response->error);
            }

            $response =  $response->data;

            $details =  collect($response->notifications)->pluck("details")->collapse();
            $notificationsCollection = DetailResource::collection($details);

            $data = [
                "notifications"  => $notificationsCollection,
                "current_page"   => $response->current_page,
                "next_page"      => $response->next_page,
                "previous_page"  => $response->previous_page,
                "total_pages"    => $response->total_pages,
            ];

            return $this->returnData($data);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }



}
