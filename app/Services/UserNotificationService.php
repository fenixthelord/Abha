<?php

namespace app\Services;

use App\Models\Department;
use App\Models\Position;
use App\Models\User;
use Exception;

class UserNotificationService
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function getUserIds($data)
    {
        $result = collect();


        if (!empty($data['department_ids']) && is_array($data['department_ids'])) {
            $departments = Department::whereIn('id', $data['department_ids'])->get();
            if ($departments->isEmpty()) {
                throw new Exception("No departments found");
            }
            foreach ($departments as $department) {

                foreach ($department->employees as $employee) {
                    $result->push([
                        'user_id'       => $employee->id,
                        'department_id' => $employee->department_id,
                    ]);
                }
            }
        }


        if (!empty($data['position_ids']) && is_array($data['position_ids'])) {
            $positions = Position::whereIn('id', $data['position_ids'])->get();
            if ($positions->isEmpty()) {
                throw new Exception("No positions found");
            }
            foreach ($positions as $position) {

                foreach ($position->users as $user) {
                    $result->push([
                        'user_id'       => $user->id,
                        'department_id' => $user->department_id,
                    ]);
                }
            }
        }


        if (!empty($data['user_ids']) && is_array($data['user_ids'])) {
            $users = User::whereIn('id', $data['user_ids'])->get()->keyBy('id');
            $transformed = collect($data['user_ids'])->map(function ($userId) use ($users) {
                return [
                    'user_id'       => $userId,
                    'department_id' => $users->has($userId) ? $users[$userId]->department_id : null,
                ];
            });
            $result = $result->merge($transformed);
        }


        $result = $result->unique('user_id');

        return $result->toArray();
    }


    public function sendNotification($data, $userAuth)
    {
        $userIds = $this->getUserIds($data);

        if (empty($userIds)) {
            return true;
            //throw new Exception("No users found for the provided parameters");
        }


        $notificationData = [
            'sender_id'        => $userAuth->id,
            'sender_type'      => $data['sender_type'] ?? 'user',
            'sender_service'   => $data['sender_service'] ?? 'user_service',
            'title'            => $data['title'],
            'body'             => $data['body'],
            'user_ids'         => $userIds,
            'receiver_service' =>  'user_service',
            'receiver_type'    =>  'user',
            'group_id'         =>  null,
            'channel'          =>  'fcm',
            'image'            =>  null,
            'url'              =>  null,
            'object_data'      => $data['object_data'] ?? null,
        ];

        $dd = $this->notificationService->postCall('/send-notification', $notificationData);

        return $dd;
    }

}
