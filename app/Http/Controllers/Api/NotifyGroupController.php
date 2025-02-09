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
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Http\Traits\Paginate;
use Illuminate\Support\Facades\Http;
class NotifyGroupController extends Controller {
    use Firebase, ResponseTrait, Paginate;

    // Create a new notify group
    public function createNotifyGroup(Request $request) {
       // DB::beginTransaction();
        try {
            /*$request->validate([
                'name' => 'required|array|max:255',
                'name.en' => 'required|string|unique:notify_groups,name->en',
                'name.ar' => 'required|string|unique:notify_groups,name->ar',
                'description' => 'required|array',
                'description.en' => 'required|string',
                'description.ar' => 'required|string',
                'user_uuids' => 'required|array',
       //         'user_uuids.*' => 'exists:users,uuid',
            ]);
            $notifyGroup = NotifyGroup::create([
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'model' => $request->input('model'),
            ]);
            $this->addUsersToNotifyGroup($request, $notifyGroup->uuid);
            DB::commit();
            return $this->returnData('group', GroupResource::make($notifyGroup));
        } catch (Exception $e) {
            DB::rollBack();
            return $this->badRequest($e->getMessage());
        }*/
           /* $url = trim(env('NOTIFY_SERV'), '/') . '/group/add';
            dd(env('NOTIFY_SERV'));*/
            $req=$request->all();
            return   (Http::post("http://smartabha-notification.test/api/group/add",$req))->json();
        } catch (Exception $e) {

            return $this->handleException($e);}}


    // Add users to a notify group
    public function addUsersToNotifyGroup(Request $request, $notifyGroupUuid) {
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
    public function removeUsersFromNotifyGroup(Request $request, $notifyGroupUuid) {
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
    public function sendNotificationToNotifyGroup(Request $request, $notifyGroupUuid) {
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
    public function allGroup(Request $request) {
        try {
            /*$perPage = request()->input('perPage', 10);
            $pageNumber = request()->input('page', 1);
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
            return $this->PaginateData('groups', GroupResource::collection($notifyGroups), $notifyGroups);*/
            $fildes = ['name->ar','name->en'];
            $group = $this->allWithSearch(new NotifyGroup(), $fildes, $request);
            $data['group'] = GroupResource::collection($group);
            return $this->PaginateData($data, $group);

         //   $data['groups'] = GroupResource::collection($notifyGroups);

   //         return $this->PaginateData($data, $notifyGroups);
        } catch (\Exception $e) {
            return $this->returnError(__('validation.custom.notifyGroup.failed_to_retrieve_groups : ') . $e->getMessage());
        }
    }

    public function groupDetail($groupUuid) {
        try {
            if ($group = NotifyGroup::where('uuid', $groupUuid)->first()) {
                $data['group'] = GroupResource::make($group);
  //              $data['members'] = UserResource::collection($group->users);
                return $this->returnData($data);
            } else {
                return $this->badRequest(__('validation.custom.notifyGroup.group_not_found'));
            }
        } catch (Exception $e) {
            return $this->badRequest($e->getMessage());
        }
    }

    public function editGroup(Request $request, $groupUuid) {
        DB::beginTransaction();
        try {
            if ($group = NotifyGroup::whereuuid($groupUuid)->first()) {
                $request->validate([
                    'name' => 'nullable|array',
                    'name.en' => ['required_with:name', 'string', Rule::unique('notify_groups', 'name->en')->ignore($group->id)],
                    'name.ar' => ['required_with:name', 'string', Rule::unique('notify_groups', 'name->ar')->ignore($group->id)],
                    'description' => 'nullable|array',
                    'description.en' => 'nullable|string',
                    'description.ar' => 'nullable|string',
                    'model' => 'nullable|string',
                    'user_uuids' => 'nullable|array',
                    'user_uuids.*' => 'exists:users,uuid',
                ]);
                $group->name = $request->name ?? $group->name;
                $group->description = $request->description ?? $group->description;
                $group->model = $request->model ?? $group->model;
                $group->save();
                if ($request->has('user_uuids')) {
                    $group->users()->sync($request->user_uuids);
                }

                DB::commit();
                return $this->returnData('group', GroupResource::make($group));
            } else {
                return $this->badRequest(__('validation.custom.notifyGroup.group_not_found'));
            }
        } catch (Exception $e) {
            DB::rollBack();
            return $this->badRequest($e->getMessage());
        }
    }

    public function deleteNotifyGroup(Request $request, $notifyGroupUuid) {
        try {
            DB::beginTransaction();
            $validated = Validator::make(['notifyGroupId' => $notifyGroupUuid], [
                'notifyGroupId' => 'required|string|exists:notify_groups,uuid',
            ]);
            if ($validated->fails()) {
                return $this->returnValidationError($validated);
            }
            if ($notifyGroup = NotifyGroup::where('uuid', $request->notifyGroupId)->first()) {
                $notifyGroup->users()->delete();
                $notifyGroup->delete();
                DB::commit();
                return $this->returnSuccessMessage(__('validation.custom.notifyGroup.group_deleted'));
            } else {
                return $this->badRequest(__('validation.custom.notifyGroup.group_not_found'));
            }
        } catch (Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }
}
