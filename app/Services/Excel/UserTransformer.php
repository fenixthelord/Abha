<?php
namespace App\Services\Excel;

use App\Http\Resources\Permissions\NewPermissionsResource;
use App\Models\User;

class UserTransformer
{
    public function transform(User $user): array
    {
        return [
            'First Name' => $user->first_name,
            'Last Name' => $user->last_name,
            'Email' => $user->email,
            'Phone' => $user->phone,
            'Department (en)' => $user->department_id ? $user->department->getTranslation('name', 'en') : 'no department',
            'Department (ar)' => $user->department ? $user->department->getTranslation('name', 'ar') : 'بدون قسم',
            'Job' => $user->job,
            'Job ID' => $user->job_id,
            'Gender' => $user->gender,
            'Position (en)' => $user->position ? $user->position()->getTranslation('name', 'en') : 'no position',
            'Position (ar)' => $user->position ? $user->position()->getTranslation('name', 'ar') : 'لا منصب',
            'role' => count($user->getRoleNames()) == 0 ? 'no role' : $user->getRoleNames(),
            'permission' => count($user->getPermissionNames()) == 0 ? 'no permission' : $user->getPermissionNames(),
        ];
    }
}
