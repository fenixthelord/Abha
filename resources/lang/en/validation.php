<?php

return [
    'custom' => [
        'department_uuid' => [
            'required' => 'The department UUID field is required.',
            'exists' => 'The selected department UUID is invalid.',
            'unique' => 'This department already exists.',
            'uuid' => 'The UUID is invalid.',
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
