<?php
function messageValidation()
{
    return [
        'first_name.required' => 'First Name is required.',
        'first_name.min' => 'First Name must be at least 3 characters.',
        'first_name.max' => 'First Name must be less than 255 characters.',
        'first_name.string' => 'First Name must be a string.',
        'first_name.regex' => 'First Name must be a string.',
        'last_name.required' => 'Last Name is required.',
        'last_name.min' => 'Last Name must be at least 3 characters.',
        'last_name.max' => 'Last Name must be less than 255 characters.',
        'last_name.string' => 'Last Name must be a string.',
        'last_name.regex' => 'Last Name must be a string.',
        'email.required' => 'Email is required.',
        'email.email' => 'Email is not valid.',
        'email.unique' => 'Email is already in use.',
        'email.max' => 'Email must be less than 255 characters.',
        'password.required' => 'Password is required.',
        'password.min' => 'Password must be at least 8 characters.',
        'password.string' => 'Password must be a string.',
        'password.regex' => 'It must contain at least one lowercase letter, one uppercase letter, and one number.',
        'user.required' => 'Email is required.',
        'password.confirmed' => 'Password does not match.',
        'phone.required' => 'Phone is required.',
        'phone.unique' => 'Phone is already in use.',
        'phone.numeric' => 'Phone must be a number.',
        'gender.required' => 'Gender is required.',
        'gender.in' => 'Gender must be a male or female.',
        'alt.string' => 'Alt must be a string.',
        'job.string' => 'Jop must be a string.',
        'job_id.' => 'Jop must be a number.',
        'old_password.required' => 'Old Password is required.',
        'old_password.min' => 'Old Password must be at least 8 characters.',
        'old_password.string' => 'Old Password must be a string.',
        'uuid.required' => 'Uuid is required.',
        'uuid.string' => 'Uuid must be a string.',
        'uuid.unique' => 'Uuid is already in use.',
        'uuid.exists' => 'Uuid is invalid.',
        'active.required' => 'Active is required.',
        'active.in' => 'Active must be a boolean.',
        'image.required' => 'Image is required.',
        'image.image' => 'Image must be a image.',
        'image.mimes' => 'Image must be a file of type: jpeg, jpg, png.',
        'image.max' => 'Image must be less than 2MB.',
        'type.required' => 'Type is required.',
        'roleName.unique' => 'Role name is already in use.',
        'roleName.required' => 'Role name is required.',
        'roleName.exists' => 'this role doesnt exist.',
        'roleName.regex' => 'Role name must be without spaces.',
        'displayName.required.'=>'Display Name is required.',
        'displayName.unique'=>'Display Name is already in use.',
        'description.required' => 'Description is required.',
        'description.string' => 'Description must be a string.',
        'permission.required' => 'Permission is required.',
        'role.required' => 'Role is required.',
        'role.exists' => 'this role doesnt exist.',
        'name.required' => 'Name is required.',
    ];
}
if (!function_exists('SupportedLanguages')) {
    function SupportedLanguages()
    {
        return ['en','ar'];
    }
}
?>
