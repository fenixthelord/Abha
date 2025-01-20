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


class UserController extends Controller
{

    use FileUploader;
    use ResponseTrait;

    public function index(Request $request)
    {
        DB::beginTransaction();
        try {
            $pageNumber = request()->input('page', 1);
            $perPage = request()->input('perPage', 10);
            if ($request->search) {
                return $this->oldSearch(request());
            }
            $users = User::paginate($perPage, ['*'], 'page', $pageNumber);

            if ($pageNumber > $users->lastPage() || $pageNumber < 1 || $perPage < 1) {
                return $this->badRequest('Invalid page number');
            }
            $data = [
                'users' => UserResource::collection($users),
                'current_page' => $users->currentPage(),
                'next_page' => $users->nextPageUrl(),
                'previous_page' => $users->previousPageUrl(),
                'total_pages' => $users->lastPage(),
            ];
            DB::commit();
            return $this->returnData('data', $data, 'success');
        } catch (\Exception $e) {
            DB::rollBack();
            abort(400, $e->getMessage());
        }
    }

    public function Update(Request $request)
    {
        DB::beginTransaction();
        try {
            $user = auth()->user();

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
                'job_id.' => 'Jop must be a number.',
            ];
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
            ], $messages);
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
            // dd( $request->job ? $request->job : $user->job);
            $user->gender = $request->gender ? $request->gender : $user->gender;
            $user->alt = $request->alt ? $request->alt : $user->alt;
            $user->job = $request->job ? $request->job : $user->job;
            $user->job_id = $request->job_id ? $request->job_id : $user->job_id;
            $user->image = $request->image ? $request->image : $user->image;
            if ($request->has('password') && !empty($request->password)) {
                if ($request->has('old_password')) {
                    if ($user->password == Hash::make($request->old_password)) {
                        $user->password = $request->password ? Hash::make($request->password) : null;
                        $user->tokens()->delete();
                    } else {
                        return $this->returnError('Old password is wrong');
                    }
                }else{
                    return $this->returnError('Old password is required');
                }
            }
            $user->save();
            DB::commit();
            return $this->returnData('data', UserResource::make($user), 'success');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->returnError($e->getMessage());
        }
    }

    public function UpdateAdmin(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'uuid' => 'required|string|exists:users,uuid',
            ]);
            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }
            if ($user = User::whereuuid($request->uuid)->first()) {
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
                    'job_id.' => 'Jop must be a number.',
                ];
                $validator = Validator::make($request->all(), [
                    'first_name' => 'nullable|string|regex:/^[\p{Arabic}a-zA-Z\s]+$/u|min:3|max:255',
                    'last_name' => 'nullable|string|regex:/^[\p{Arabic}a-zA-Z\s]+$/u|min:3|max:255',
                    'email' => ['nullable', 'email', Rule::unique('users', 'email')->ignore($user->id), 'max:255'],
                    'phone' => ['nullable', Rule::unique('users', 'phone')->ignore($user->id), 'numeric','regex:/^05\d{8}$/'],
                    'gender' => 'nullable|in:male,female',
                    'alt' => 'nullable|string',
                    'job' => 'nullable|string',
                    'job_id' => 'nullable|string',
                    'image' => 'nullable|string',
                    'password' =>
                        'nullable|string|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|confirmed',
                    'old_password' => 'nullable|required_with:password|string',
                ], $messages);
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
                        if ($user->password == Hash::make($request->old_password)) {
                            $user->password = $request->password ? Hash::make($request->password) : null;
                            $user->tokens()->delete();
                        } else {
                            return $this->returnError('Old password is wrong');
                        }
                    }else{
                        return $this->returnError('Old password is required');
                    }
                }
                $user->save();
                DB::commit();
                return $this->returnData('data', UserResource::make($user), 'success');
            } else {
                return $this->returnError('User not found');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->returnError($e->getMessage());
        }
    }

    public function deleteUser(Request $request)
    {
        DB::beginTransaction();
        try {

            $validator = Validator::make($request->all(), [
                'uuid' => 'required|string|exists:users',
            ]);
            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }
            if ($user = User::whereuuid($request->uuid)->first()) {
                $user->delete();
                DB::commit();
                return $this->returnSuccessMessage('User deleted successfully');
            } else {
                return $this->badRequest('User Deleted already.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->returnError($e->getMessage());
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
            })->paginate($perPage, ['*'], 'page', $pageNumber)){
            if ($pageNumber > $users->lastPage() || $pageNumber < 1 || $perPage < 1) {
                return $this->badRequest('Invalid page number');
            }
            $data = [
                'users' => UserResource::collection($users),
                'current_page' => $users->currentPage(),
                'next_page' => $users->nextPageUrl(),
                'previous_page' => $users->previousPageUrl(),
                'total_pages' => $users->lastPage(),
            ];
            DB::commit();
            return $this->returnData('data', $data, 'success');
            }else{
                return $this->badRequest('Invalid search');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            abort(400, $e->getMessage());
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
                    $data = [
                        'users' => UserResource::collection($users),
                        'current_page' => $users->currentPage(),
                        'next_page' => $users->nextPageUrl(),
                        'previous_page' => $users->previousPageUrl(),
                        'total_pages' => $users->lastPage(),
                    ];
                    return $this->returnData('data', $data, 'success');
                } else {
                    return $this->returnData('user', 'No results found');
                }
            }
        } catch (Exception $e) {
            return $this->returnError($e->getMessage());
        }
    }


    public function showDeleteUser()
    {
        try {
        $pageNumber = request()->input('page', 1);
        $perPage = request()->input('perPage', 10);
        if($users = User::onlyTrashed()->paginate($perPage, ['*'], 'page', $pageNumber)){
        if ($pageNumber > $users->lastPage() || $pageNumber < 1 || $perPage < 1) {
            $data = [
                'users' => UserResource::collection($users),
                'current_page' => $users->currentPage(),
                'next_page' => $users->nextPageUrl(),
                'previous_page' => $users->previousPageUrl(),
                'total_pages' => $users->lastPage(),
            ];
            return $this->returnData('data', $data, 'success');
        } else {
            return $this->returnData('user', 'Invalid page number');
        }
        }else return $this->badRequest('No results found');
        }catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }
    }

    public function restoreUser(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'uuid' => 'required|string|exists:users,uuid',
            ]);
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
            return $this->returnError($e->getMessage());
        }
    }

    public function addImage(Request $request)
    {
        DB::beginTransaction();
        try {
            $messages = [
                'image.required' => 'Image is required.',
                'image.image' => 'Image must be a image.',
                'image.mimes' => 'Image must be a file of type: jpeg, jpg, png.',
                'image.max' => 'Image must be less than 2MB.',
                'type.required' => 'Type is required.',
            ];
            $validator = Validator::make($request->all(), [
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'type' => 'required|string',
            ], $messages);
            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }
            $image = $this->uploadImagePublic($request, $request->type);
            DB::commit();
            return $this->returnData('data', $image, 'Image Uploaded');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->returnError($ex->getMessage());
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
                return $this->returnData('error', 'OTP Not expired');
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
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->returnError($ex->getMessage());
        }
    }

    public function verifyOtp(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'otp' => 'required|string',
            ]);
            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }
            $user = User::where('id', auth()->user()->id)
                ->where('otp_code', $request->otp)
                ->where('otp_expires_at', '>', now())
                ->firstorfail();
            if (!$user) {
                return $this->returnError('Invalid OTP Or Expired');
            }
            $user->otp_code = null;
            $user->otp_expires_at = null;
            $user->otp_verified = true;
            $user->save();
            DB::commit();
            $this->returnSuccessMessage('OTP verified successfully');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->returnError($ex->getMessage());
        }
    }

    public function user_profile()
    {
        $user = auth()->user();
        $data = [
            'user' => UserResource::make($user),
            'roles' => $user->role,
        ];
        return $this->returnData('user', $data);
    }
}
