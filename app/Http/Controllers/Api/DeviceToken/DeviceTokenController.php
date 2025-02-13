<?php

namespace App\Http\Controllers\Api\DeviceToken;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeviceToken\SaveDeviceTokenRequest;
use App\Http\Resources\Notifications\NotificationResource;
use App\Http\Traits\ResponseTrait;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class DeviceTokenController extends Controller
{
    use ResponseTrait;
    protected $notificationService;
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function saveDeviceToken(SaveDeviceTokenRequest $request)
    {
        try {
            // Prepare the data to be sent to the external notification service
            $data = [
                'token'         => $request->input('token'),
                'owner_id'      => $request->input('user_id'),
                'owner_type'   => 'user',
                'owner_service' => 'user_service',
                'channel' => 'fcm'
            ];

            // Send a POST request using the NotificationService
            $response = $this->notificationService->postCall('/device-tokens/add', $data);

            // If the response indicates an error, return an error response using the trait
            if (isset($response['error'])) {
                return $this->returnError($response['error']);
            }


            // Return a successful response
            return $this->returnSuccessMessage('Device token saved successfully');
        } catch (\Exception $e) {
            // Handle any exceptions using the handleException method from the ResponseTrait
            return $this->handleException($e);
        }
    }

    public function getReceivedNotifications() {
        try {
            $user = request()->user('sanctum');

            if (!$user) {
                return $this->Unauthorized(__('validation.custom.customer_not_authenticate'));
            }
            // if you are login go to next
            $requestData = [
                'receiver_id' => $user->id,
            ];
            $response = $this->notificationService->getCall('/notifications', $requestData);
            $response = json_decode(json_encode($response, true));

            if (isset($response->error)) {
                return $this->returnError($response->error);
            }
            if (!isset($response->data)) {
                return $this->returnError(__('validation.user.invalid_response_data'));
            }

            $response = $response->data;

            $details = collect($response->notifications)->pluck("details")->collapse();
            $notificationsCollection = \App\Http\Resources\Notifications\NotificationResource::collection($details);

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
            $notificationsCollection = NotificationResource::collection($details);

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
