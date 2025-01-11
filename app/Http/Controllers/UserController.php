<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Http\Traits\FileUploader;
use Illuminate\Support\Str;
use App\Models\User;
use App\Http\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class UserController extends Controller
{

    use FileUploader;
    use ResponseTrait;

    public function register(Request $request)
    {
        $messages = ['first_name.required' => 'First Name is required.',
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
            'phone.required' => 'Phone is required.',
            'phone.unique' => 'Phone is already in use.',
            'phone.numeric' => 'Phone must be a number.',
            'gender.required' => 'Gender is required.',
            'gender.in' => 'Gender must be a male or female.',
            'alt.string' => 'Alt must be a string.',
            'jop.string' => 'Jop must be a string.',
            'jop_id.numeric' => 'Jop must be a number.',];
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|regex:/^[a-zA-Z\s]+$/|min:3|max:255',
            'last_name' => 'required|string|regex:/^[a-zA-Z\s]+$/|min:3|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'password' =>
                'required|string|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|confirmed',
            'phone' => 'required|unique:users,phone|numeric',
            'gender' => 'required|in:male,female',
            'alt' => 'nullable|string',
            'jop' => 'nullable|string',
            'jop_id' => 'nullable|numeric',
        ], $messages);
        if ($validator->fails()) {
            return $this->returnValidationError($validator,null,$validator->errors()->first());
        }
        try {
            $user = User::create([
                'uuid' => Str::uuid(),
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => $request->password ? Hash::make($request->password) : null,
                'alt' => $request->alt,
                'gender' => $request->gender,
                'jop' => $request->jop,
                'jop_id' => $request->jop_id,
                'OTP' => '00000',
            ]);
            return $this->returnSuccessMessage("Registered successfully");

        } catch (\Exception $ex) {
            return $this->returnError($ex->getMessage());
        }
    }


    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user' => 'required|string',
                'password' =>
                    'required|string|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/',
            ]);
            if ($validator->fails()) {
                return $this->returnValidationError($validator,null,$validator->errors()->first());
            }
            $username = filter_var($request->user, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
            $user = User::where($username, $request->user)->first();
            if (!$user || !Hash::check($request->password, $user->password)) {
                return $this->returnValidationError($validator, 400, 'email or phone or password false');
            } else {
                $data['user'] = UserResource::make($user);
                $data['token'] = $user->createToken('MyApp')->plainTextToken;
                return $this->returnData('data', $data);
            }
        } catch
        (\Exception $ex) {
            return $this->returnError($ex->getMessage());
        }
    }

    public function addImage(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',

            ]);
            if ($validator->fails()) {
                return $this->returnValidationError($validator,null,$validator->errors()->first());
            }
        //    $data = auth()->user();
        //     $image = $this->uploadImagePublic($request, $data, $request->type);
           // dd($request->file('image'));
            $image=$request->file('image')->store('app/images');

            return $this->returnData('data',$image,'Image Uploaded');
        } catch (\Exception $ex) {
            return $this->returnError($ex->getMessage());
        }
    }


    public function logout(Request $request)
    {
        try {

            Auth::user()->tokens()->delete();
            return $this->returnSuccessMessage("logged out");
        } catch (\Exception $ex) {
            return $this->returnError($ex->getMessage());
        }
    }
}

