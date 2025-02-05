<?php

return [
    'custom' => [
        'department_uuid' => [
            'required' => 'The department UUID field is required.',
            'exists' => 'The selected department UUID is invalid.',
            'unique' => 'This department already exists.',
            'uuid' => 'The UUID is invalid.',
        ],

        'roleAndPerm' => [


            'failed_to_obtain_token' => 'Failed to obtain token',
            'role_created_successfully' => 'Role created successfully',
            'role_not_found' => 'Role not found',
            'user_not_found' => 'User not found',
            'permission_not_found' => 'Permission not found',
            'role_assigned_successfully' => 'Role assigned successfully',
            'permission_assigned_successfully' => 'Permission assigned successfully',
            'role_removed_successfully' => 'Role removed successfully',
            'permission_removed_successfully' => 'Permission removed successfully',
            'role_deleted_successfully' => 'Role deleted successfully',
            'master_role_cannot_be_deleted' => 'Master role cannot be deleted',
            'master_permission_cannot_be_assigned' => 'Master permission cannot be assigned',
            'validation_error' => 'Validation error',
            'forbidden_action' => 'You are not authorized to perform this action',
        ],

        'userController' => [
            'user_not_found' => 'User not found',
            'invalid_page' => 'Invalid page number',
            'permission_denied' => "You don't have permission to access this page",
            'user_activated' => 'User activated successfully',
            'user_deactivated' => 'User deactivated successfully',
            'image_uploaded' => 'Image uploaded successfully',
            'otp_sent' => 'OTP sent successfully',
            'otp_verified' => 'OTP verified successfully',
            'invalid_otp' => 'Invalid OTP Or Expired',
            'otp_expired' => 'OTP Not expired',
            'user_restore' => 'User restore successfully',
            'results' => 'No results found',
            'invalid_search' => 'Invalid search',
            'user_not_deleted' => 'User Not Deleted',
            'user_deleted_already' => 'User Deleted already',
            'deleted_successfully' => 'User deleted successfully',
            'master_can_not_be_deleted' => 'This user is Master and can not be deleted',
            'can_not_be_activated_or_deactivated' => 'This user is Master account , it can not be activated or deactivated',
            'user_is_deleted' => 'This user is deleted',
            'old_password_wrong' => 'Old password is wrong',
            'old_password_required' => 'Old password is required',
            'master_account_can_not_updated' => 'This user is Master account and can not be updated',
            'dont_have_permission_to_access' => 'you dont have permission to access this page',
        ],

        'firebase' => [
            'notification_failed' => 'Failed to send notification. FCM returned HTTP code: ',
            'failed_to_obtain_token' => 'Failed to obtain access token',
            'failed_to_send_notification' => 'Failed to send notification: ',
        ],

        'responseTrait' => [
            'duplicate_entry_found' => 'Duplicate entry found',
            'cannot_delete_or_update' => 'Cannot delete or update as it is referenced elsewhere',
            'foreign_key_constraint_violation' => 'Foreign key constraint violation',
            'category_cannot_parent' => 'A category cannot be its own parent',
            'database_error' => 'Database error: ',
        ],

        'notifyGroup' => [
            'group_created' => 'Group created successfully',
            'group_not_found' => 'Group not found',
            'users_added' => 'Users added to notify group successfully',
            'users_removed' => 'Users removed from notify group successfully',
            'notifications_sent' => 'Notifications sent successfully',
            'failed_to_send_notifications' => 'Failed to send notifications',
            'group_deleted' => 'Notify group deleted successfully',
            'failed_to_retrieve_groups' => 'Failed to retrieve notify groups: ',
            'no_users_in_group' => 'Group does not have users',
            'no_device_tokens' => 'No device tokens found for this notify group',
            'validation_failed' => 'Validation failed',
        ],

        'language' => [
            'lang_success' => 'Language changed successfully',
        ],

        'notification' => [
            'notification_sent_success' => 'Notifications sent successfully!',
            'notification_sent_fail' => 'Failed to send notifications.',
            'device_token_saved' => 'Device Token saved successfully.',
            'no_device_tokens' => 'No device tokens found for the specified users or group.',
            'invalid_page_number' => 'Invalid page number.',
            'user_not_found' => 'User not found.',
            'validation_error' => 'Validation error.',
        ],

        'name' => [
            'required' => 'The name field is required.',
            'array' => 'The name must be sent as an array.',
        ],
        'name.en' => [
            'required' => 'The English name field is required.',
            'string' => 'The English name must be a string.',
            'min' => 'The English name must be at least :min characters.',
            'max' => 'The English name may not exceed :max characters.',
            'unique' => 'This English name already exists in this department.',
        ],
        'name.ar' => [
            'required' => 'The Arabic name field is required.',
            'string' => 'The Arabic name must be a string.',
            'min' => 'The Arabic name must be at least :min characters.',
            'max' => 'The Arabic name may not exceed :max characters.',
            'unique' => 'This Arabic name already exists in this department.',
        ],
        'chields' => [
            'required' => 'The children field is required.',
            'array' => 'The children must be an array.',
        ],
        'uuid' => [
            'required' => 'The UUID field is required.',
            'exists' => 'The selected UUID is invalid or has been deleted.',
            'unique' => 'This UUID already exists.',
            'uuid' => 'The UUID is invalid.',
        ],
        'first_name' => [
            'required' => 'First Name is required.',
            'min' => 'First Name must be at least 3 characters.',
            'max' => 'First Name must be less than 255 characters.',
            'string' => 'First Name must be a string.',
            'regex' => 'First Name must be a string.',
        ],
        'last_name' => [
            'required' => 'Last Name is required.',
            'min' => 'Last Name must be at least 3 characters.',
            'max' => 'Last Name must be less than 255 characters.',
            'string' => 'Last Name must be a string.',
            'regex' => 'Last Name must be a string.',
        ],
        'email' => [
            'required' => 'Email is required.',
            'email' => 'Email is not valid.',
            'unique' => 'Email is already in use.',
            'max' => 'Email must be less than 255 characters.',
        ],
        'password' => [
            'required' => 'Password is required.',
            'min' => 'Password must be at least 8 characters.',
            'string' => 'Password must be a string.',
            'regex' => 'It must contain at least one lowercase letter, one uppercase letter, and one number.',
            'confirmed' => 'Password does not match.',
        ],

        'category' => [
            'category_deleted' => 'Category and all related subcategories were deleted successfully.',
            'category_updated' => 'Categories updated successfully.',
            'category_created' => 'Categories created successfully.',
        ],

        'user' => [
            'required' => 'Email is required.',
        ],
        'phone' => [
            'required' => 'Phone is required.',
            'unique' => 'Phone is already in use.',
            'numeric' => 'Phone must be a number.',
        ],
        'gender' => [
            'required' => 'Gender is required.',
            'in' => 'Gender must be a male or female.',
        ],
        'alt' => [
            'string' => 'Alt must be a string.',
        ],
        'job' => [
            'string' => 'Job must be a string.',
        ],
        'job_id' => [
            'numeric' => 'Job must be a number.',
        ],
        'old_password' => [
            'required' => 'Old Password is required.',
            'min' => 'Old Password must be at least 8 characters.',
            'string' => 'Old Password must be a string.',
        ],
        'active' => [
            'required' => 'Active is required.',
            'in' => 'Active must be a boolean.',
        ],
        'image' => [
            'required' => 'Image is required.',
            'image' => 'Image must be an image.', 'mimes' => 'Image must be a file of type: jpeg, jpg, png.',
            'max' => 'Image must be less than 2MB.',
        ],
        'type' => [
            'required' => 'Type is required.',
        ],
        'roleName' => [
            'unique' => 'Role name is already in use.',
            'required' => 'Role name is required.',
            'exists' => 'This role does not exist.',
            'regex' => 'Role name must be without spaces.',
        ],
        'displayName' => [
            'required' => 'Display Name is required.',
            'unique' => 'Display Name is already in use.',
        ],
        'description' => [
            'required' => 'Description is required.',
            'string' => 'Description must be a string.',
        ],
        'permission' => [
            'required' => 'Permission is required.',
        ],
        'role' => [
            'required' => 'Role is required.',
            'exists' => 'This role does not exist.',
        ],
        'forget_password' => [
            'sent_code' => 'otp send successfully.',
            'error' => 'try again later.',
            'expired' => 'otp not expired.',
            'done' => 'change password successfully and logout from all devices.',
            'not_done' => 'the verification code is not valid.',
        ],
        'auth' => [
            'permission' => 'you are not allowed to access this page.',
            'failed' => 'email or password is wrong.',
            'deleted' => 'this account is deleted.',
            'logout' => 'loged out.',
        ],
        'department' => [
            'notfound' => 'department not found.',
            'try' => 'try again.',
            'done' => 'department added successfully.',
            'deleted' =>'department already deleted.',
            'delete' => 'department deleted successfully.',
        ],
    ],

    'attributes' => [
        'department_uuid' => 'Department UUID',
        'name' => 'Name',
        'name.en' => 'English Name',
        'name.ar' => 'Arabic Name',
        'chields' => 'Children',
        'uuid' => 'UUID',
        'first_name' => 'First Name',
        'last_name' => 'Last Name',
        'email' => 'Email',
        'password' => 'Password',
        'user' => 'Email',
        'phone' => 'Phone',
        'gender' => 'Gender',
        'alt' => 'Alt',
        'job' => 'Job',
        'job_id' => 'Job',
        'old_password' => 'Old Password',
        'active' => 'Active',
        'image' => 'Image',
        'type' => 'Type',
        'roleName' => 'Role Name',
        'displayName' => 'Display Name',
        'description' => 'Description',
        'permission' => 'Permission',
        'role' => 'Role',
    ],
];
