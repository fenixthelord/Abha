<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Traits\FileUploader;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Http\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class UserController extends Controller
{

    use FileUploader;
    use ResponseTrait;
    public function index()
    {
        $users = User::paginate(10);
        return $this->returnData('users', UserResource::collection($users), 'success');
    }
    public function Update(Request $request)
    {
        try {
            $messages = [
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
                'password.confirmed' => 'Password does not match.',
                'old_password.required' => 'Old Password is required.',
                'old_password.min' => 'Old Password must be at least 8 characters.',
                'old_password.string' => 'Old Password must be a string.',
                'phone.required' => 'Phone is required.',
                'phone.unique' => 'Phone is already in use.',
                'phone.numeric' => 'Phone must be a number.',
                'gender.required' => 'Gender is required.',
                'gender.in' => 'Gender must be a male or female.',
                'alt.string' => 'Alt must be a string.',
                'job.string' => 'Jop must be a string.',
                'job_id.' => 'Jop must be a number.',];
            $validator = Validator::make($request->all(), [
                'first_name' => 'nullable|string|regex:/^[\p{Arabic}a-zA-Z\s]+$/u|min:3|max:255',
                'last_name' => 'nullable|string|regex:/^[\p{Arabic}a-zA-Z\s]+$/u|min:3|max:255',
                'email' => 'nullable|email|unique:users,email|max:255',
                'phone' => 'nullable|unique:users,phone|numeric',
                'gender' => 'nullable|in:male,female',
                'alt' => 'nullable|string',
                'job' => 'nullable|string',
                'job_id' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
                'type' => 'nullable|required_with:image|string',
                'password' =>
                    'nullable|string|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|confirmed',
                'old_password' => 'nullable|required_with:password|string|min:8',
            ],$messages);
            if ($validator->fails()) {
                return $this->returnValidationError($validator,null,$validator->errors());
            }
            $user = auth()->user();

            $user->first_name = $request->firt_name ? $request->first_name : $user->first_name;
            $user->last_name = $request->last_name ? $request->last_name : $user->last_name;
            $user->email = $request->email ? $request->email : $user->email;
            $user->phone = $request->phone ? $request->phone : $user->phone;
            $user->gender = $request->gender ? $request->gender : $user->gender;
            $user->alt = $request->alt ? $request->alt : $user->alt;
            $user->job = $request->job ? $request->job : $user->job;
            $user->job_id = $request->job_id ?$request->job_id : $user->job_id;
            if ($request->hasFile('image')) {
                $user->image = $this->uploadImagePublic($request,$request->type);
            }
            if ($request->has('password')) {
                if ($user->password == $request->old_password);
                {
                    $user->password = $request->password ? Hash::make($request->password) : null;
                }
            }
            $user->save();
            return $this->returnSuccessMessage('User updated successfully');
        }
        catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }
    }
    public function addImage(Request $request)
    {
        try {
            $messages = ['image.required' => 'Image is required.',
                'image.image' => 'Image must be a image.',
                'image.mimes' => 'Image must be a file of type: jpeg, jpg, png.',
                'image.max' => 'Image must be less than 2MB.',
                'type.required' => 'Type is required.',];
            $validator = Validator::make($request->all(), [
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'type' => 'required|string',
            ], $messages);
            if ($validator->fails()) {
                return $this->returnValidationError($validator, null, $validator->errors()->first());
            }
            $data = auth()->user();
            $image = $this->uploadImagePublic($request, $request->type);
            return $this->returnData('data', $image, 'Image Uploaded');
        } catch (\Exception $ex) {
            return $this->returnError($ex->getMessage());
        }
    }
    public function sendOTP()
    {
        $user = auth()->user();
        $otp = $user->OTP = rand(10000, 99999);
        $user->save();
        $mail = Mail::to($user->email)->send(new OtpMail($otp));
        if ($mail) {
            return $this->returnSuccessMessage('OTP send successfully');
        }
    }
}

