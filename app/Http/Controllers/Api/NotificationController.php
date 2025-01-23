<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Http\Traits\Firebase;
use App\Http\Traits\ResponseTrait;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\DeviceToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    use Firebase, ResponseTrait;

    public function sendNotification(Request $request)
    {

        DB::beginTransaction();

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
            DB::commit();
            return $status
                ? $this->returnSuccessMessage('Notifications sent successfully!')
                : $this->returnError('Failed to send notifications.');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->returnError($e->getMessage());
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
            return $this->returnError('Failed to save device token', $e->getMessage());
        }
    }

    public function allNotification(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                "perPage" => 'nullable|integer|min:9'
            ]);
            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }
            $customer = $request->user();
            $pageNumber = $request->input('page', 1);
            $perPage = $request->input("perPage", 10);
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
            ];
            return $this->returnData("data", $data);
        } catch (\Exception $ex) {
            return $this->badRequest($ex->getMessage());
        }
    }
}
