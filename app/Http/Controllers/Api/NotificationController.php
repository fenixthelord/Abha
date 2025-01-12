<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\Firebase;
use App\Http\Traits\ResponseTrait;
use Illuminate\Http\Request;
use App\Models\DeviceToken;

class NotificationController extends Controller {
    use Firebase, ResponseTrait;
    public function sendNotification(Request $request) {

        $tokens = $request->input('tokens', []);
        $content = [
            'title' => $request->input('title'),
            'body' => $request->input('body'),
            'type' => $request->input('data.type', null),
            'object' => $request->input('data.object', null),
            'screen' => $request->input('data.screen', null),
        ];

        if (empty($tokens)) {
            return $this->badRequest('Tokens are required.');
        }
        if (empty($content['title']) || empty($content['body'])) {
            return $this->badRequest('Title and body are required.');
        }

        try {
            $status = $this->HandelDataAndSendNotify($tokens, $content);
            return $status
                ? $this->returnSuccessMessage('Notifications sent successfully!')
                : $this->returnError('Failed to send notifications.');
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }
    }

    public  function saveDeviceToken(Request $request) {
        $request->validate([
            'token' => 'required|string|unique:device_tokens,token',
            'user_id' => 'nullable|exists:users,id',
        ]);

        try {
            DeviceToken::create ([
               'token' => $request->input('token'),
               'user_id' => $request->input('user_id'),
            ]);
            return $this->returnSuccessMessage('Device Token saved successfully');
        } catch (\Exception $e) {
            return $this->returnError('Faild to save device token', $e->getMessage());
        }
    }
}
