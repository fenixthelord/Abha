<?php

namespace App\Http\Controllers\Api;

use App\Events\SendOtpPhone;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Traits\FileUploader;
use App\Mail\OtpMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Http\Traits\ResponseTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Http\Traits\Paginate;


class UserController extends Controller
{

    use FileUploader;
    use ResponseTrait;
    use Paginate;

    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasPermissionTo('user.show')) {
            return $this->Forbidden("you don't have permission to access this page");
        }
        try {

           /* $perPage = request()->input('perPage', 10);
             $pageNumber = request()->input('page', 1);*/
           /*  if ($request->search) {
                 return $this->oldSearch(request());
             }
             $users = User::whereDoesntHave('roles', function ($query) {
                 $query->where('name', 'Master');
             })->paginate($perPage, ['*'], 'page', $pageNumber);
             if ($pageNumber > $users->lastPage() || $pageNumber < 1 || $perPage < 1) {
                 return $this->badRequest('Invalid page number');
             }*/
            $fields = ['phone', 'email', 'last_name', 'first_name'];
            $users = $this->allWithSearch(new User(), $fields, $request);
            $data['users'] = UserResource::collection($users);
            return $this->PaginateData($data, $users);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function Update(Request $request)
    {
        DB::beginTransaction();
        try {
            $user = auth()->user();
            $validator = Validator::make($request->all(), [
                'first_name' => 'nullable|string|regex:/^[\p{Arabic}a-zA-Z\s]+$/u|min:3|max:255',
                'last_name' => 'nullable|string|regex:/^[\p{Arabic}a-zA-Z\s]+$/u|min:3|max:255',
                'email' => ['nullable', 'email', Rule::unique('users', 'email')->ignore($user->id), 'max:255'],
                'phone' => ['nullable', Rule::unique('users', 'phone')->ignore($user->id), 'numeric', 'regex:/^05\d{8}$/'],
                'gender' => 'nullable|in:male,female',
                'alt' => 'nullable|string',
                'job' => 'nullable|string',
                'job_id' => 'nullable|string',
                'image' => 'nullable|string',
                'password' =>
                    'nullable|string|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|confirmed',
                'old_password' => 'nullable|required_with:password|string',
            ], messageValidation());
            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }
            $user->first_name = $request->first_name ? $request->first_name : $user->first_name;
            $user->last_name = $request->last_name ? $request->last_name : $user->last_name;
            if ($request->has('email') && !empty($request->email)) {
                $user->email = $request->email;
                $user->otp_verified = false;
            }
            if ($request->has('phone') && !empty($request->phone)) {
                $user->phone = $request->phone;
                $user->otp_verified = false;
            }
            $user->gender = $request->gender ? $request->gender : $user->gender;
            $user->alt = $request->alt ? $request->alt : $user->alt;
            $user->job = $request->job ? $request->job : $user->job;
            $user->job_id = $request->job_id ? $request->job_id : $user->job_id;
            $user->image = $request->image ? $request->image : $user->image;
            if ($request->has('password') && !empty($request->password)) {
                if ($request->has('old_password')) {
                    if (Hash::check($request->old_password, $user->password)) {
                        $user->password = $request->password ? Hash::make($request->password) : null;
                        $user->tokens()->where('id', '!=', $user->currentAccessToken()->id)->delete();
                    } else {
                        return $this->badRequest('Old password is wrong');
                    }
                } else {
                    return $this->badRequest('Old password is required');
                }
            }
            $user->save();
            $data['data'] = UserResource::make($user);
            DB::commit();
            return $this->returnData($data);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }

    public function updateAdmin(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasPermissionTo('user.update')) {
            return $this->Forbidden("you don't have permission to access this page");
        }
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'uuid' => 'required|string|exists:users,uuid',
            ], messageValidation());
            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }
            if ($user = User::whereuuid($request->uuid)->first()) {
                if ($user->hasRole("Master")) {
                    return $this->Forbidden('This user is Master account and can not be updated');
                }
                $validator = Validator::make($request->all(), [
                    'first_name' => 'nullable|string|regex:/^[\p{Arabic}a-zA-Z\s]+$/u|min:3|max:255',
                    'last_name' => 'nullable|string|regex:/^[\p{Arabic}a-zA-Z\s]+$/u|min:3|max:255',
                    'email' => ['nullable', 'email', Rule::unique('users', 'email')->ignore($user->id), 'max:255'],
                    'phone' => ['nullable', Rule::unique('users', 'phone')->ignore($user->id), 'numeric', 'regex:/^05\d{8}$/'],
                    'gender' => 'nullable|in:male,female',
                    'alt' => 'nullable|string',
                    'job' => 'nullable|string',
                    'job_id' => 'nullable|string',
                    'image' => 'nullable|string',
                    'password' =>
                        'nullable|string|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|confirmed',
                    'old_password' => 'nullable|required_with:password|string',
                    'role' => "nullable|array",
                    "role.*" => "nullable|string|exists:roles,name",
                ], messageValidation());
                if ($validator->fails()) {
                    return $this->returnValidationError($validator);
                }
                $user->first_name = $request->first_name ? $request->first_name : $user->first_name;
                $user->last_name = $request->last_name ? $request->last_name : $user->last_name;
                if ($request->has('email') && !empty($request->email)) {
                    $user->email = $request->email;
                    $user->otp_verified = false;
                }
                if ($request->has('phone') && !empty($request->phone)) {
                    $user->phone = $request->phone;
                    $user->otp_verified = false;
                }
                $user->gender = $request->gender ? $request->gender : $user->gender;
                $user->alt = $request->alt ? $request->alt : $user->alt;
                $user->job = $request->job ? $request->job : $user->job;
                $user->job_id = $request->job_id ? $request->job_id : $user->job_id;
                $user->image = $request->image ? $request->image : $user->image;
                if ($request->has('password') && !empty($request->password)) {
                    if ($request->has('old_password')) {
                        if (Hash::check($request->old_password, $user->password)) {
                            $user->password = $request->password ? Hash::make($request->password) : null;
                            $user->tokens()->delete();
                        } else {
                            return $this->badRequest('Old password is wrong');
                        }
                    } else {
                        return $this->badRequest('Old password is required');
                    }
                }
                $user->save();
                if ($request->role) {
                    $user->syncRoles($request->role);
                }

                $data['data'] = UserResource::make($user);
                DB::commit();
                return $this->returnData($data);
            } else {
                return $this->badRequest('User not found');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }

    public function active(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasPermissionTo('user.restore')) {
            return $this->Forbidden("you don't have permission to access this page");
        }


        try {
            $validator = Validator::make($request->all(), [
                'uuid' => 'required|string|exists:users,uuid',
                'active' => 'required|in:0,1',
            ], messageValidation());
            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }
            if (User::whereuuid($request->uuid)->onlyTrashed()->first()) {
                return $this->badRequest('This user is deleted');
            } else {
                if ($user = User::whereuuid($request->uuid)->first()) {
                    if ($user->hasRole("Master")) {
                        return $this->Forbidden('This user is Master account , it can not be activated or deactivated');
                    }
                    $user->active = $request->active;
                    $user->save();
                    if ($user->active == 1) {
                        return $this->returnSuccessMessage('User activated');
                    } elseif ($user->active == 0) {
                        return $this->returnSuccessMessage('User not activated');
                    }
                } else {
                    return $this->badRequest('User not found');
                }
            }
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function deleteUser(Request $request)
    {
        $current_user = auth()->user();
        if ($current_user && !$current_user->hasPermissionTo('user.delete')) {
            return $this->Forbidden("you don't have permission to access this page");
        }

        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'uuid' => 'required|string|exists:users,uuid',
            ], messageValidation());

            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }
            $selected_user = User::whereuuid($request->uuid)->first();
            if ($selected_user){
                if ($selected_user->hasRole("Master") || $selected_user->id == 1) {
                    return $this->Forbidden('This user is Master and can not be deleted');
                }
                $selected_user->delete();
                DB::commit();
                return $this->returnSuccessMessage('User deleted successfully');
            } else {
                return $this->badRequest('User Deleted already.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }

    public function oldSearch(Request $request)
    {
        DB::beginTransaction();
        try {
            $pageNumber = request()->input('page', 1);
            $perPage = request()->input('perPage', 10);
            $search = $request->search;
            if ($users = User::where(function ($query) use ($search) {
                $query->where('id', 'like', "%$search%")
                    ->orWhere('uuid', 'like', "%$search%")
                    ->orWhere('first_name', 'like', "%$search%")
                    ->orWhere('last_name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%")
                    ->orWhere('job', 'like', "%$search%")
                    ->orWhere('job_id', 'like', "%$search%");
            })->paginate($perPage, ['*'], 'page', $pageNumber)) {
                if ($pageNumber > $users->lastPage() || $pageNumber < 1 || $perPage < 1) {
                    return $this->badRequest('Invalid page number');
                }

                $data['users'] = UserResource::collection($users);
                DB::commit();
                return $this->PaginateData($data, $users);
            } else {
                return $this->badRequest('Invalid search');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }


    public function searchUser(Request $request)
    {
        try {
            $pageNumber = request()->input('page', 1);
            $perPage = request()->input('perPage', 10);
            $query = User::query();
            $fillable = (new User)->getFillable();
            foreach ($request->all() as $key => $value) {
                if (in_array($key, $fillable) && !empty($value)) {
                    $query->where($key, 'LIKE', '%' . $value . '%');
                    $users = $query->paginate($perPage, ['*'], 'page', $pageNumber);
                    if ($pageNumber > $users->lastPage() || $pageNumber < 1 || $perPage < 1) {
                        return $this->badRequest('Invalid page number');
                    }

                    $data['users'] = UserResource::collection($users);
                    return $this->PaginateData($data, $users);
                } else {
                    return $this->returnSuccessMessage('No results found');
                }
            }
        } catch (Exception $e) {
            return $this->handleException($e);
        }
    }


    public function showDeleteUser()
    {

        try {
            $user = auth()->user();
            if (!$user->hasPermissionTo('user.delete')) {
                return $this->Forbidden("you don't have permission to access this page");
            }
            $pageNumber = request()->input('page', 1);
            $perPage = request()->input('perPage', 10);
            if ($users = User::onlyTrashed()->paginate($perPage, ['*'], 'page', $pageNumber)) {
                if ($pageNumber <= $users->lastPage() && $pageNumber >= 1 && $perPage >= 1) {
                    $data['users'] = UserResource::collection($users);
                    return $this->PaginateData($data, $users);
                } else {
                    return $this->badRequest('Invalid page number');
                }
            } else return $this->returnSuccessMessage('No results found');
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function restoreUser(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasPermissionTo('user.restore')) {
            return $this->Forbidden("you don't have permission to access this page");
        }
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'uuid' => 'required|string|exists:users,uuid',
            ], messageValidation());
            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }
            if ($user = User::whereuuid($request->uuid)->onlyTrashed()->first()) {
                $user->restore();
                DB::commit();
                return $this->returnSuccessMessage('User restore successfully');
            } else {
                return $this->badRequest('User Not Deleted.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }

    public function addImage(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'type' => 'required|string',
            ], messageValidation());
            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }
            $image = $this->uploadImagePublic($request, $request->type);

            DB::commit();
            return $this->returnData($image, 'Image Uploaded');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }

    /*    public function sendOTP()
        {
            $user = auth()->user();
            $otp = $user->OTP = rand(100000, 999999);
            $user->otp_expires_at = Carbon::now()->addMinutes(5);
            $user->save();
            $mail = Mail::to($user->email)->send(new OtpMail($otp));
            if ($mail) {
                return $this->returnSuccessMessage('OTP send successfully');
            }
        }*/
    public function sendOtp()
    {
        DB::beginTransaction();
        try {
            $user = auth()->user();
            if (Carbon::now()->lessThan($user->otp_expires_at) || $user->otp_verified == true) {
                return $this->badRequest('OTP Not expired');
            }
            $otp = $user->otp_code = rand(100000, 999999);
            $user->otp_expires_at = Carbon::now()->addMinutes(5);
            $user->save();
            //$phone = event(new SendOtpPhone($otp, $user->phone));
            DB::commit();
            $phone = Mail::to($user->email)->send(new OtpMail($user->otp_code));
            if ($phone) {
                return $this->returnSuccessMessage('OTP send successfully');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }

    public function verifyOtp(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'otp' => 'required|string',
            ], messageValidation());
            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }
            $user = User::where('id', auth()->user()->id)
                ->where('otp_code', $request->otp)
                ->where('otp_expires_at', '>', now())
                ->firstorfail();
            if (!$user) {
                return $this->badRequest('Invalid OTP Or Expired');
            }
            $user->otp_code = null;
            $user->otp_expires_at = null;
            $user->otp_verified = true;
            $user->save();
            DB::commit();
            $this->returnSuccessMessage('OTP verified successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }

    public function user_profile()
    {
        $user = auth()->user();
        $data = [
            'user' => UserResource::make($user),
            'roles' => $user->role,
        ];
        return $this->returnData($data);
    }
}
