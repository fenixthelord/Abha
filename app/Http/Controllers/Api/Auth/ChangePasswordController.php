<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Http\Traits\ResponseTrait;
use App\Models\Role\Role;

use function Laravel\Prompts\text;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ChangePasswordController extends Controller
{
    use ResponseTrait;

    public function forgotPassword(Request $request)
    {
        DB::beginTransaction();
        try {
            $input = $request->only('email');
            $validator = Validator::make($input, [
                'email' => "required|email|" . Rule::exists('users', 'email')->whereNull('deleted_at')
            ]);
            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }
            $user = User::where('email', $request->email)->first();
            // dd($user->otp_expires_at  );
            if ($user->otp_expires_at == null ? false : Carbon::now()->lessThan($user->otp_expires_at)) {
                return $this->badRequest('OTP Not expired');
            } elseif (Carbon::now()->isAfter($user->otp_expires_at) || $user->otp_expires_at == null) {
                $verificationCode = rand(100000, 999999);
                $user->verify_code = $verificationCode;
                $user->otp_expires_at = Carbon::now()->addMinutes(5);
                DB::commit();
                $user->save();
                Mail::to($user->email)->send(new OtpMail($verificationCode));
                return $this->returnSuccessMessage('Verification code sent!');
            } else {
                DB::rollBack();
                return $this->returnError('You have to try again');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->returnError($e->getMessage());
        }
    }

    public function reset_password(Request $request)
    {
        try {
            DB::beginTransaction();
            $input = $request->only('email', 'code', 'password', 'password_confirmation');
            $validator = Validator::make($input, [
                'email'     => 'required|email|exists:users,email',
                'password' =>
                'required|string|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|confirmed',
                'code'      => 'required|integer|min_digits:6|max_digits:6',
            ]);
            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }
            if ($user = User::where('email', $request->email)
                ->where('verify_code', $request->code)
                ->where('otp_expires_at', '>', now())
                ->first()
            ) {
                $user->update([
                    'password' => Hash::make($request->password),
                    'verify_code' => null,
                    'otp_expires_at' => null
                ]);
                $user->tokens()->delete();
                // dd($user);
                DB::commit();
                return $this->returnSuccessMessage("Password changed and You've been logged out of all your sessions");
            } else {
                return $this->badRequest('The verification code is invalid!');
                DB::commit();
                return $this->returnSuccessMessage('Password changed!');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->returnError($e->getMessage());
        }
    }
}
