<?php

namespace App\Http\Controllers\Api\Auth;

use App\Events\SendOtpPhone;
use App\Events\UserLogin;
use App\Events\UserRegistered;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Http\Traits\ResponseTrait;
use  App\Http\Traits\FileUploader;

class UserAuthController extends Controller
{
    use ResponseTrait;
    use FileUploader;

    public function register(Request $request)
    {
        DB::beginTransaction();
        try {
            $messages = [
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
                'password.confirmed' => 'Password does not match.',
                'phone.required' => 'Phone is required.',
                'phone.unique' => 'Phone is already in use.',
                'phone.numeric' => 'Phone must be a number.',
                'gender.required' => 'Gender is required.',
                'gender.in' => 'Gender must be a male or female.',
                'alt.string' => 'Alt must be a string.',
                'job.string' => 'Jop must be a string.',
                'job_id.' => 'Jop must be a number.',
            ];
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|regex:/^[\p{Arabic}a-zA-Z\s]+$/u|min:3|max:255',
                'last_name' => 'required|string|regex:/^[\p{Arabic}a-zA-Z\s]+$/u|min:3|max:255',
                'email' => 'required|email|unique:users,email|max:255',
                'password' =>
                'required|string|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|confirmed',
                'phone' => 'required|unique:users,phone|numeric',
                'gender' => 'required|in:male,female',
                'alt' => 'nullable|string',
                'job' => 'nullable|string',
                'job_id' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
            ], $messages);
            if ($validator->fails()) {
                return $this->returnValidationError($validator, null, $validator->errors());
            }
            $user = User::create([
                'uuid' => Str::orderedUuid(),
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => $request->password ? Hash::make($request->password) : null,
                'alt' => $request->alt,
                'gender' => $request->gender,
                'job' => $request->job,
                'job_id' => $request->job_id,
                'image' => $request->image,
                'otp_code' => rand(100000, 999999),
                'otp_expires_at' => Carbon::now()->addMinutes(5),
            ]);
            if ($user) {
                event(new UserRegistered($user));
            }
            DB::commit();
            //     event(new sendOtpPhone($user->otp, $user->phone));
            $data['token'] = $user->createToken('MyApp')->plainTextToken;
            return $this->returnData('data', $data);
        } catch
        (\Exception $ex) {
            DB::rollBack();
            return $this->returnError($ex->getMessage());
        }
    }

    public function login(Request $request)
    {
        try {
            $messages = [
                'user.required' => 'Email is required.',
                'password.required' => 'Password is required.',
                'password.min' => 'Password must be at least 8 characters.',
                'password.string' => 'Password must be a string.',
                'password.regex' => 'It must contain at least one lowercase letter, one uppercase letter, and one number.',
            ];
            $validator = Validator::make($request->all(), [
                'user' => ['required', 'string',
                    function ($attribute, $value, $fail) {
                        $field = filter_var($value, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
                        if (!\DB::table('users')->where($field, $value)->exists()) {
                            $fail("The :attribute does not exist in our records.");
                        }
                    }],
                'password' =>
                'required|string',
            ], $messages);
            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }
            $username = filter_var($request->user, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
            if (User::where($username, $request->user)->firstorfail()) {
                $user = User::where($username, $request->user)->firstorfail();
            } else {
                return $this->returnValidationError($validator, 400, 'email or phone or password false');
            }

            if (!$user || !Hash::check($request->password, $user->password)) {
                return $this->Unauthorized('email or phone or password false');
            } else {
                //               event(new UserLogin($user));
                $data['user'] = UserResource::make($user);
                $data['token'] = $user->createToken('MyApp')->plainTextToken;
                return $this->returnData('data', $data);
            }
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
