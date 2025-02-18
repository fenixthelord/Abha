<?php

namespace App\Http\Controllers\Api;

use App\Events\SendOtpPhone;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Traits\FileUploader;
use App\Mail\OtpMail;
use App\Models\Role\Role;
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

    public function __construct()
    {
        $permissions = [
            'index'  => ['user.show'],
            'show'  => ['user.show'],
            'store' => ['user.create'],
            'update'    => ['user.update'],
            'updateAdmin'    => ['user.update'],
            'deleteUser'   => ['user.delete'],
            'showDeleteUser'   => ['user.delete'],
            'restoreUser'    => ['user.restore'],
            'active'    => ['user.restore'],
        ];

        foreach ($permissions as $method => $permission) {
            $this->middleware('permission:' . implode('|', $permission))->only($method);
        }
    }
    public function index(Request $request)
    {
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
            $user->image = $request->image;
            if ($request->has('password') && !empty($request->password)) {
                if ($request->has('old_password')) {
                    if (Hash::check($request->old_password, $user->password)) {
                        $user->password = $request->password ? Hash::make($request->password) : null;
                        $user->tokens()->where('id', '!=', $user->currentAccessToken()->id)->delete();
                    } else {
                        return $this->badRequest(__('validation.custom.userController.old_password_wrong'));
                    }
                } else {
                    return $this->badRequest(__('validation.custom.userController.old_password_required'));
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
//        if (!$user->hasPermissionTo('user.update')) {
//            return $this->Forbidden(__('validation.custom.userController.permission_denied'));
//        }
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|string|exists:users,id',
            ], messageValidation());
            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }
            if ($user = User::whereId($request->id)->first()) {
                if ($user->hasRole("Master")) {
                    return $this->Forbidden(__('validation.custom.userController.master_account_can_not_updated'));
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
                $user->image = $request->image;
                if ($request->has('password') && !empty($request->password)) {
                    if ($request->has('old_password')) {
                        if (Hash::check($request->old_password, $user->password)) {
                            $user->password = $request->password ? Hash::make($request->password) : null;
                            $user->tokens()->delete();
                        } else {
                            return $this->badRequest(__('validation.custom.userController.old_password_wrong'));
                        }
                    } else {
                        return $this->badRequest(__('validation.custom.userController.old_password_required'));
                    }
                }
                $user->save();
                if ($request->role) {
                //    $user->syncRoles($request->role);
                    $id = [];
                    foreach ($request->role as $role) {
                        $id = array_merge($id,Role::where('name',$role)->pluck('id')->toArray());
                    }
                    $user->auditSync('roles', $id);
                }

                $data['data'] = UserResource::make($user);
                DB::commit();
                return $this->returnData($data);
            } else {
                return $this->badRequest(__('validation.custom.userController.user_not_found'));
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }

    public function active(Request $request)
    {
        $user = auth()->user();
//        if (!$user->hasPermissionTo('user.restore')) {
//            return $this->Forbidden(__('validation.custom.userController.permission_denied'));
//        }

        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|string|exists:users,id',
                'active' => 'required|in:0,1',
            ], messageValidation());
            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }
            if (User::whereId($request->id)->onlyTrashed()->first()) {
                return $this->badRequest('This user is deleted');
            } else {
                if ($user = User::whereId($request->id)->first()) {
                    if ($user->hasRole("Master")) {
                        return $this->Forbidden(__('validation.custom.userController.can_not_be_activated_or_deactivated'));
                    }
                    $user->active = $request->active;
                    $user->save();
                    if ($user->active == 1) {
                        return $this->returnSuccessMessage(__('validation.custom.userController.user_activated'));
                    } elseif ($user->active == 0) {
                        return $this->returnSuccessMessage(__('validation.custom.userController.user_deactivated'));
                    }
                } else {
                    return $this->badRequest(__('validation.custom.userController.user_not_found'));
                }
            }
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function deleteUser(Request $request)
    {
        $current_user = auth()->user();
//        if ($current_user && !$current_user->hasPermissionTo('user.delete')) {
//            return $this->Forbidden(__('validation.custom.userController.permission_denied'));
//        }

        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|string|exists:users,id',
            ], messageValidation());

            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }
            $selected_user = User::whereId($request->id)->first();
            if ($selected_user) {
                if ($selected_user->hasRole("Master") || $selected_user->id == '11953802-99ad-4961-b7a6-bed53b1004ea') {
                    return $this->Forbidden(__('validation.custom.userController.master_can_not_be_deleted'));
                }
                $selected_user->delete();
                DB::commit();
                return $this->returnSuccessMessage(__('validation.custom.userController.deleted_successfully'));
            } else {
                return $this->badRequest(__('validation.custom.userController.user_deleted_already'));
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
                    ->orWhere('first_name', 'like', "%$search%")
                    ->orWhere('last_name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%")
                    ->orWhere('job', 'like', "%$search%")
                    ->orWhere('job_id', 'like', "%$search%");
            })->paginate($perPage, ['*'], 'page', $pageNumber)) {
                if ($pageNumber > $users->lastPage() || $pageNumber < 1 || $perPage < 1) {
                    return $this->badRequest(__('validation.custom.userController.invalid_page'));
                }

                $data['users'] = UserResource::collection($users);
                DB::commit();
                return $this->PaginateData($data, $users);
            } else {
                return $this->badRequest(__('validation.custom.userController.invalid_search'));
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
                        return $this->badRequest(__('validation.custom.userController.invalid_page'));
                    }

                    $data['users'] = UserResource::collection($users);
                    return $this->PaginateData($data, $users);
                } else {
                    return $this->returnSuccessMessage(__('validation.custom.userController.results'));
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
//            if (!$user->hasPermissionTo('user.delete')) {
//                return $this->Forbidden(__('validation.custom.userController.permission_denied'));
//            }
            $pageNumber = request()->input('page', 1);
            $perPage = request()->input('perPage', 10);
            if ($users = User::onlyTrashed()->paginate($perPage, ['*'], 'page', $pageNumber)) {
                if ($pageNumber <= $users->lastPage() && $pageNumber >= 1 && $perPage >= 1) {
                    $data['users'] = UserResource::collection($users);
                    return $this->PaginateData($data, $users);
                } else {
                    return $this->badRequest(__('validation.custom.userController.invalid_page'));
                }
            } else return $this->returnSuccessMessage(__('validation.custom.userController.results'));
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function restoreUser(Request $request)
    {
        $user = auth()->user();
//        if (!$user->hasPermissionTo('user.restore')) {
//            return $this->Forbidden(__('validation.custom.userController.permission_denied'));
//        }
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|string|exists:users,id',
            ], messageValidation());
            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }
            if ($user = User::whereId($request->id)->onlyTrashed()->first()) {
                $user->restore();
                DB::commit();
                return $this->returnSuccessMessage(__('validation.custom.userController.user_restore'));
            } else {
                return $this->badRequest(__('validation.custom.userController.user_not_deleted'));
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
            return $this->returnData($image, __('validation.custom.userController.image_uploaded'));
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
                return $this->badRequest(__('validation.custom.userController.otp_expired'));
            }
            $otp = $user->otp_code = rand(100000, 999999);
            $user->otp_expires_at = Carbon::now()->addMinutes(5);
            $user->save();
            //$phone = event(new SendOtpPhone($otp, $user->phone));
            DB::commit();
            $phone = Mail::to($user->email)->send(new OtpMail($user->otp_code));
            if ($phone) {
                return $this->returnSuccessMessage(__('validation.custom.userController.otp_sent'));
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
                return $this->badRequest(__('validation.custom.userController.invalid_otp'));
            }
            $user->otp_code = null;
            $user->otp_expires_at = null;
            $user->otp_verified = true;
            $user->save();
            DB::commit();
            $this->returnSuccessMessage(__('validation.custom.userController.otp_verified'));
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }

    public function userProfile()
    {
        $user = auth()->user();
        $data = [
            'user' => UserResource::make($user),
            'roles' => $user->role,
        ];
        return $this->returnData($data);
    }
    public function show(Request $request){
      try{  $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|exists:users,id',
        ]);
        if ($validator->fails()) {
            return $this->returnValidationError($validator);
        }
        $user=User::where('id',$request->user_id)->first();

       return $this->returnData(UserResource::make($user));

    }
    catch(\Exception $e){
          return $this->handleException($e);
    }
}}
