<?php

namespace App\Services;

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
        $userIds = [];

        // معالجة مصفوفة department_ids
        if (!empty($data['department_ids']) && is_array($data['department_ids'])) {
            $departments = Department::whereIn('id', $data['department_ids'])->get();
            if ($departments->isEmpty()) {
                throw new Exception("No departments found");
            }
            foreach ($departments as $department) {
                $userIds = array_merge($userIds, $department->employees()->pluck('id')->toArray());
            }
        }

        // معالجة مصفوفة position_ids
        if (!empty($data['position_ids']) && is_array($data['position_ids'])) {
            $positions = Position::whereIn('id', $data['position_ids'])->get();
            if ($positions->isEmpty()) {
                throw new Exception("No positions found");
            }
            foreach ($positions as $position) {
                $userIds = array_merge($userIds, $position->users()->pluck('id')->toArray());
            }
        }

        // معالجة user_ids المُرسلة مباشرة
        if (!empty($data['user_ids']) && is_array($data['user_ids'])) {
            $userIds = array_merge($userIds, User::whereIn('id', $data['user_ids'])->pluck('id')->toArray());
        }

        return array_unique($userIds);
    }

    public function sendNotification($data, $userAuth)
    {
        $userIds = $this->getUserIds($data);

        if (empty($userIds)) {
            throw new Exception("No users found for the provided parameters");
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
        dd($dd);
        return $dd;
    }

}
