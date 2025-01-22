<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GroupResource;
use App\Models\DeviceToken;
use App\Models\NotifyGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Traits\Firebase;
use App\Http\Traits\ResponseTrait;

class NotifyGroupController extends Controller
{
    use Firebase, ResponseTrait;

    // Create a new notify group
    public function createNotifyGroup(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:notify_groups,name',
            'description' => 'nullable|string',
        ]);

        $notifyGroup = NotifyGroup::create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'model' => $request->input('model'),
        ]);

        return $this->returnData('group', GroupResource::make($notifyGroup));
    }

    // Add users to a notify group
    public function addUsersToNotifyGroup(Request $request, $notifyGroupUuid)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $notifyGroup = NotifyGroup::where('uuid', $notifyGroupUuid)->firstOrFail();
        $notifyGroup->users()->syncWithoutDetaching($request->input('user_ids'));

        return $this->returnSuccessMessage('Users added to notify group successfully');
    }

    // Remove users from a notify group
    public function removeUsersFromNotifyGroup(Request $request, $notifyGroupUuid)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $notifyGroup = NotifyGroup::where('uuid', $notifyGroupUuid)->firstOrFail();
        $notifyGroup->users()->detach($request->input('user_ids'));

        return $this->returnSuccessMessage('Users removed from notify group successfully');
    }

    // Send notification to a notify group
    public function sendNotificationToNotifyGroup(Request $request, $notifyGroupUuid)
    {
        $notifyGroup = NotifyGroup::where('uuid', $notifyGroupUuid)->firstOrFail();
        $userIds = $notifyGroup->users()->pluck('users.id');
        $tokens = DeviceToken::whereIn('user_id', $userIds)->pluck('token')->toArray();

        if (empty($tokens)) {
            return $this->badRequest('No device tokens found for this notify group.');
        }

        $content = [
            'title' => $request->input('title'),
            'body' => $request->input('body'),
            'type' => $request->input('data.type', null),
            'object' => $request->input('data.object', null),
            'screen' => $request->input('data.screen', null),
        ];

        try {
            $status = $this->HandelDataAndSendNotify($tokens, $content);
            return $status
                ? $this->returnSuccessMessage('Notifications sent successfully!')
                : $this->returnError('Failed to send notifications.');
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }
    }

    public function allGroup(Request $request)
    {
        try {
            // Fetch all notify groups with optional pagination
            $perPage = $request->input('per_page', 10); // Default to 10 items per page
            $notifyGroups = NotifyGroup::paginate($perPage);

            return $this->returnData('groups',GroupResource::collection($notifyGroups));
        } catch (\Exception $e) {
            return $this->returnError('Failed to retrieve notify groups: ' . $e->getMessage());
        }
    }
}
