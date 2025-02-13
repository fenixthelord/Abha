<?php

namespace App\Http\Controllers\Api\DeviceToken;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeviceToken\SaveDeviceTokenRequest;
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
                'channel' =>'fcm'
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

}
