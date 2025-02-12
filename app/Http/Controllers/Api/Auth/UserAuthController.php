<?php

namespace App\Http\Controllers\Api\Auth;

use App\Events\UserRegistered;
use App\Http\Controllers\Controller;
use App\Http\Resources\CustomPermissionResource;
use App\Http\Resources\UserResource;
use App\Http\Traits\FileUploader;
use App\Http\Traits\ResponseTrait;
use App\Models\Department;
use App\Models\Organization;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;


class UserAuthController extends Controller
{
    use ResponseTrait;
    use FileUploader;

    public function register(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasPermissionTo("user.create")) {
            return $this->Forbidden(__('validation.custom.auth.permission'));
        }
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|regex:/^[\p{Arabic}a-zA-Z\s]+$/u|min:3|max:255',
                'last_name' => 'required|string|regex:/^[\p{Arabic}a-zA-Z\s]+$/u|min:3|max:255',
                'email' => 'required|email|unique:users,email|max:255',
                'password' =>
                'required|string|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|confirmed',
                'phone' => 'required|unique:users,phone|numeric|regex:/^05\d{8}$/',
                'gender' => 'required|in:male,female',
                'alt' => 'nullable|string',
                'job' => 'nullable|string',
                'job_id' => 'nullable|string',
                'image' => 'nullable|string',
                'role' => 'nullable|array',
                'role.*' => 'string|exists:roles,name',
                'department_id' => ["nullable", "string", Rule::exists('departments', 'id')->where("deleted_at", null)],
            ], messageValidation());
            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }
            $department = Department::where("id", $request->department_id)->firstorFail();

            $user = User::create([
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
                'department_id' => $department->id,

            ]);
            if (!$request->role) {
                $user->assignRole('employee'); // Default role
            } else {
                if ($request->role != "Master") {
                    $user->syncRoles($request->role);
                } else {
                    $user->assignRole('Master_employee');
                }
            }
            if ($user) {
                event(new UserRegistered($user));
            }
            //     event(new sendOtpPhone($user->otp, $user->phone));
            $data['token'] = $user->createToken('MyApp')->plainTextToken;

            // Generate a refresh token
            $refreshToken = Str::random(60);
            $user->update([
                'refresh_token' => Hash::make($refreshToken),
                'refresh_token_expires_at' => Carbon::now()->addDays(config('refresh_token_expires_at')), // Customize expiry as needed
            ]);
            // Include refresh token in the response
            $data['refresh_token'] = $refreshToken;
            DB::commit();
            return $this->returnData($data);
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->badRequest($ex->getMessage());
        }
    }

    public function login(Request $request)
    {

        try {
            $validator = Validator::make($request->all(), [
                'user' => [
                    'required',
                    'string',
                    function ($attribute, $value, $fail) {
                        $field = filter_var($value, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
                        if (!User::where($field, $value)->exists()) {
                            $fail(__('validation.custom.auth.failed'));
                        }
                    }
                ],
                'password' => 'required|string',
            ], messageValidation());
            if ($validator->fails()) {
                return $this->returnValidationError($validator, 401);
            }
            $username = filter_var($request->user, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
            if (User::where($username, $request->user)->onlyTrashed()->first()) {
                return $this->badRequest(__('validation.custom.auth.deleted'));
            } elseif ($user = User::where($username, $request->user)->first()) {
                if (!$user || !Hash::check($request->password, $user->password)) {
                    return $this->Unauthorized(__('validation.custom.auth.failed'));
                } else {
                    //               event(new UserLogin($user));
                    $data['user'] = UserResource::make($user);
                    if ($user->hasRole('Master')) {
                        $data['custom_permissions'] = [['action' => 'manage', 'subject' => 'all']];
                    } else {
                        $data['custom_permissions'] = CustomPermissionResource::collection($user->getAllPermissions());
                    }
                    $data['token'] = $user->createToken('MyApp')->plainTextToken;

                    // Generate a refresh token
                    $refreshToken = Str::random(60);

                    $user->update([
                        'refresh_token' => Hash::make($refreshToken),
                        'refresh_token_expires_at' => Carbon::now()->addDays(config('refresh_token_expires_at')), // Customize expiry as needed
                    ]);


                    // Include refresh token in the response
                    $data['refresh_token'] = $refreshToken;


                    return $this->returnData($data);
                }
            } else {
                return $this->Unauthorized(__('validation.custom.auth.failed'));
            }
        } catch (\Exception $ex) {

            return $this->badRequest($ex->getMessage());
        }
    }

    public function logout(Request $request)
    {
        DB::beginTransaction();
        try {

            $request->user()->currentAccessToken()->delete();
            DB::commit();
            return $this->returnSuccessMessage(__('validation.custom.auth.logout'));
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->badRequest($ex->getMessage());
        }
    }

    public function refreshToken(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'refresh_token' => 'required|string',
            ]);

            // Fetch all users with a non-null refresh token
            $users = User::whereNotNull('refresh_token')->get();

            $user = null;
            foreach ($users as $u) {
                if (Hash::check($request->refresh_token, $u->refresh_token)) {
                    $user = $u;
                    break;
                }
            }

            if (!$user) {
                return $this->Unauthorized('Invalid refresh token.');
            }

            // Check if the refresh token has expired
            if ($user->refresh_token_expires_at->isPast()) {
                return $this->Unauthorized('Refresh token has expired.');
            }

            // Revoke existing tokens
            $user->tokens()->delete();

            // Generate a new access token
            $accessToken = $user->createToken('MyApp')->plainTextToken;

            // Generate a new refresh token
            $refreshToken = Str::random(60);
            $user->update([
                'refresh_token' => Hash::make($refreshToken),
                'refresh_token_expires_at' => Carbon::now()->addDays(config('refresh_token_expires_at')), // Customize expiry as needed
            ]);

            DB::commit();


            return $this->returnData([
                'token' => $accessToken,
                'refresh_token' => $refreshToken,
            ]);
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->returnError($ex->getMessage());
        }
    }
}
