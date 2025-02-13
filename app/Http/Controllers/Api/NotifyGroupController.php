<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Group\AddGroupRequest;
use App\Http\Requests\Group\editGroupRequest;
use App\Http\Requests\Group\idGroupRequest;
use App\Http\Resources\Group\GroupResource;
use App\Http\Resources\UserResource;
use App\Models\DeviceToken;
use App\Models\NotifyGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Traits\Firebase;
use App\Http\Traits\ResponseTrait;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Http\Traits\Paginate;
use Illuminate\Support\Facades\Http;
use App\Services\NotificationService;

class NotifyGroupController extends Controller
{
    use Firebase, ResponseTrait, Paginate;

    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    // Create a new notify group
    public function createNotifyGroup(AddGroupRequest $request)
    {

        try {
            $params = [
                'owner_id' => $owner = auth('sanctum')->user()->getAuthIdentifier(),
                'group_type' => $request->type,
                'group_service' => $request->type . "_service",
                'name' => $request->name,
                'description' => $request->description,
                'icon' => $request->icon,
                'department_id' => $request->department_id,
                'member_type' => $request->type,
                'user_id' => $request->user_id,
                'member_service' => $request->type . "_service"


            ];
            $method = 'api/group/add';
            $response = $this->notificationService->postCall($method, $params);


            if (!is_array($response) || !isset($response['data']['group']) || !is_array($response['data']['group'])) {
                return $this->badRequest("group not found");
            }
            $data['group'] = GroupResource::make($response['data']['group']);
            return $this->returnData($data);

        } catch (Exception $e) {

            return $this->handleException($e);
        }
    }


    // Add users to a notify group
    public function addUsersToNotifyGroup(Request $request, $notifyGroupUuid)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'user_uuids' => 'required|array',
                //           'user_uuids.*' => 'exists:users,uuid',
            ]);

            if ($notifyGroup = NotifyGroup::where('uuid', $notifyGroupUuid)->first()) {
                $notifyGroup->users()->syncWithoutDetaching($request->input('user_uuids'));
                DB::commit();
                return $this->returnSuccessMessage(__('validation.custom.notifyGroup.users_added'));
            } else {
                return $this->badRequest(__('validation.custom.notifyGroup.group_not_found'));
            }
        } catch (Exception $e) {
            DB::rollBack();
            return $this->badRequest($e->getMessage());
        }
    }

    // Remove users from a notify group
    public function removeUsersFromNotifyGroup(Request $request, $notifyGroupUuid)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'user_uuids' => 'required|array',
                'user_uuids.*' => 'exists:users,uuid',
            ]);
            if ($notifyGroup = NotifyGroup::where('uuid', $notifyGroupUuid)->first()) {
                $notifyGroup->users()->detach($request->input('user_uuids'));
                DB::commit();
                return $this->returnSuccessMessage(__('validation.custom.notifyGroup.users_removed'));
            } else {
                return $this->badRequest(__('validation.custom.notifyGroup.group_not_found'));
            }
        } catch (Exception $e) {
            DB::rollBack();
            return $this->badRequest($e->getMessage());
        }
    }

    // Send notification to a notify group
    public function sendNotificationToNotifyGroup(Request $request, $notifyGroupUuid)
    {
        if ($notifyGroup = NotifyGroup::where('uuid', $notifyGroupUuid)->first()) {

            $userIds = $notifyGroup->users()->pluck('users.id');
            if (!$userIds) {
                return $this->badRequest(__('validation.custom.notifyGroup.no_users_in_group'));
            }
            $tokens = DeviceToken::whereIn('user_id', $userIds)->pluck('token')->toArray();

            if (empty($tokens)) {
                return $this->badRequest(__('validation.custom.notifyGroup.no_device_tokens'));
            }

            $content = [
                'title' => $request->input('title'),
                'body' => $request->input('body'),
                'type' => $request->input('data.type', null),
                'object' => $request->input('data.object', null),
                'screen' => $request->input('data.screen', null),
            ];
            try {
                $request['group_uuid'] = $notifyGroupUuid;
                (new NotificationController())->store($request);
                $status = $this->HandelDataAndSendNotify($tokens, $content);
                return $status
                    ? $this->returnSuccessMessage(__('validation.custom.notifyGroup.notifications_sent'))
                    : $this->returnError(__('validation.custom.notifyGroup.failed_to_send_notifications'));
            } catch (\Exception $e) {
                return $this->returnError($e->getMessage());
            }
        } else {
            return $this->badRequest(__('validation.custom.notifyGroup.group_not_found'));
        }
    }

    public function allGroup(Request $request)
    {
        try {
            $params = $request->all();

            $method = 'api/group/';

            $response = $this->notificationService->getCall($method, $params);

            if (!is_array($response) || !isset($response['data']['groups']) || !is_array($response['data']['groups'])) {
                return $this->badRequest("group not found");
            }

            $data['group'] = GroupResource::collection($response['data']['groups']);

            return $this->returnData($data);

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function groupDetail(idGroupRequest $request)
    {
        try {
            $params = [
                'group_id' => $request->input('id'),
            ];
            $method = 'api/group/show';
            $response = $this->notificationService->postCall($method, $params);
            if (!is_array($response) || !isset($response['data']['group']) || !is_array($response['data']['group'])) {
                return $this->badRequest("group not found");
            }
            $data['group'] = GroupResource::make($response['data']['group']);
            return $this->returnData($data);
        } catch (Exception $e) {
            return $this->badRequest($e->getMessage());
        }
    }

    public function editGroup(editGroupRequest $request)
    {
        DB::beginTransaction();
        try {
            $params = [
                'group_id' => $request->group_id,
                'owner_id' =>auth('sanctum')->user()->getAuthIdentifier(),
                'group_type' => $request->type,
                'group_service' => $request->type . "_service",
                'name' => $request->name,
                'description' => $request->description,
                'icon' => $request->icon,
                'department_id' => $request->department_id,
                'member_type' => $request->type,
                'user_id' => $request->user_id,
                'member_service' => $request->type . "_service"


            ];

            $method = 'api/group/update';
            $response = $this->notificationService->putCall($method, $params);

            if (!is_array($response) || !isset($response['data']['group']) || !is_array($response['data']['group'])) {
                return $this->badRequest("group not found");
            }
            $data['group'] = GroupResource::make($response['data']['group']);
            return $this->returnData($data);

        } catch (Exception $e) {
            DB::rollBack();
            return $this->badRequest($e->getMessage());
        }
    }

    public function deleteNotifyGroup(idGroupRequest $request)
    {
        try {
            $params = [
                'group_id' => $request->group_id,
            ];
            $method = 'api/group/delete';
           $response= $this->notificationService->deleteCall($method, $params);
            if (!is_array($response) || !isset($response['data']['group']) || !is_array($response['data']['group'])) {
                return $this->badRequest("group not found");
            }
            return $this->returnSuccessMessage('the group deleted successfully');

        } catch (Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }
}
