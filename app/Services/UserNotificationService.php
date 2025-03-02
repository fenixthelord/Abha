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

        if (!empty($data['department_id'])) {
            $department = Department::find($data['department_id']);
            if (!$department) {
                throw new Exception("Department not found");
            }
            $userIds = array_merge($userIds, $department->employees()->pluck('id')->toArray());
        }

        if (!empty($data['position_id'])) {
            $position = Position::find($data['position_id']);
            if (!$position) {
                throw new Exception("Position not found");
            }
            $userIds = array_merge($userIds, $position->users()->pluck('id')->toArray());
        }

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
            'sender_id' => $userAuth->id,
            'sender_type' => $data['sender_type'] ?? 'user',
            'sender_service' => $data['sender_service'] ?? 'user_service',
            'title' => $data['title'],
            'body' => $data['body'],
            'user_ids' => $userIds,
            'receiver_service' => $data['model'] == 'user' ? 'user_service' : 'customer_service',
            'receiver_type' => $data['model'] ?? 'user',
            'group_id' => $data['group_id'] ?? null,
            'channel' => $data['channel'] ?? 'fcm',
            'image' => $data['image'] ?? null,
            'url' => $data['url'] ?? null,
        ];

        return $this->notificationService->postCall('/send-notification', $notificationData);
    }
}
