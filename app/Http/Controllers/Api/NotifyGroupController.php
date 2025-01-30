<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GroupResource;
use App\Http\Resources\UserResource;
use App\Models\DeviceToken;
use App\Models\NotifyGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Traits\Firebase;
use App\Http\Traits\ResponseTrait;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class NotifyGroupController extends Controller
{
    use Firebase, ResponseTrait;

    // Create a new notify group
    public function createNotifyGroup(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'name' => 'required|string|unique:notify_groups,name',
                'description' => 'nullable|string',
                'user_uuids' => 'required|array',
                'user_uuids.*' => 'exists:users,uuid',
            ]);
            $notifyGroup = NotifyGroup::create([
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'model' => $request->input('model'),
            ]);
            $this->addUsersToNotifyGroup($request, $notifyGroup->uuid);
            DB::commit();
            $data['group'] =  GroupResource::make($notifyGroup);
            return $this->returnData($data);
        } catch (Exception $e) {
            DB::rollBack();
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
                'user_uuids.*' => 'exists:users,uuid',
            ]);

            if ($notifyGroup = NotifyGroup::where('uuid', $notifyGroupUuid)->first()) {
                $notifyGroup->users()->syncWithoutDetaching($request->input('user_uuids'));
                DB::commit();
                return $this->returnSuccessMessage('Users added to notify group successfully');
            } else {
                return $this->badRequest('Group not found');
            }
        } catch (Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
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
                return $this->returnSuccessMessage('Users removed from notify group successfully');
            } else {
                return $this->badRequest('Group not found');
            }
        } catch (Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }

    // Send notification to a notify group
    public function sendNotificationToNotifyGroup(Request $request, $notifyGroupUuid)
    {
        if ($notifyGroup = NotifyGroup::where('uuid', $notifyGroupUuid)->first()) {

            $userIds = $notifyGroup->users()->pluck('users.id');
            if (!$userIds) {
                return $this->badRequest('Group does not  have user');
            }
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
                $request['group_uuid'] = $notifyGroupUuid;
                (new NotificationController())->store($request);
                $status = $this->HandelDataAndSendNotify($tokens, $content);
                return $status
                    ? $this->returnSuccessMessage('Notifications sent successfully!')
                    : $this->returnError('Failed to send notifications.');
            } catch (\Exception $e) {
                return $this->handleException($e);
            }
        } else {
            return $this->badRequest('Group not found');
        }
    }

    public function allGroup(Request $request)
    {
        try {
            $pageNumber = request()->input('page', 1);
            $perPage = request()->input('perPage', 10);
            $groups = NotifyGroup::query()
                ->when($request->has('search'), function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%');
                });
            $notifyGroups = $groups->paginate($perPage, ['*'], 'page', $pageNumber);
            if ($pageNumber > $notifyGroups->lastPage() || $pageNumber < 1 || $perPage < 1) {
                $pageNumber = 1;
                $notifyGroup = $groups->paginate($perPage, ['*'], 'page', $pageNumber);
                $data["groups"] = GroupResource::collection($notifyGroup);
                return $this->PaginateData($data, $notifyGroup);
            }
            $data['groups'] = GroupResource::collection($notifyGroups);
            return $this->PaginateData($data, $notifyGroups);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
    public function groupDetail($groupUuid)
    {
        try {
            if ($group = NotifyGroup::where('uuid', $groupUuid)->first()) {
                $data['group'] = GroupResource::make($group);
                $data['members'] = UserResource::collection($group->users);
                return $this->returnData($data);
            } else {
                return $this->badRequest('Group not found');
            }
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }
    public function editGroup(Request $request, $groupUuid)
    {
        DB::beginTransaction();
        try {
            if ($group = NotifyGroup::whereuuid($groupUuid)->first()) {
                $request->validate([
                    'name' => ['nullable', 'string', Rule::unique('notify_groups', 'name')->ignore($group->id)],
                    'description' => 'nullable|string',
                    'model' => 'nullable|string',
                    'user_uuids' => 'nullable|array',
                    //     'user_uuids.*' => 'exists:users,uuid',
                ]);
                $group->name = $request->name ?? $group->name;
                $group->description = $request->description ?? $group->description;
                $group->model = $request->model ?? $group->model;
                $group->save();
                $group->users()->sync($request->user_uuids);

                $data['group'] = GroupResource::make($group);

                DB::commit();
                return $this->returnData($data);
            } else {
                return $this->badRequest('Group not found');
            }
        } catch (Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }
}
