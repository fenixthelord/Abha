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
use function Laravel\Prompts\text;

class ChangePassword extends Controller
{
    use ResponseTrait;

    public function forgotPassword(Request $request)
    {
        try {
            $input = $request->only('email');
            $validator = Validator::make($input, [
                'email' => "required|email|exists:users,email"
            ]);
            if ($validator->fails()) {
                return $this->returnValidationError($validator);
            }
            $user = User::where('email', $request->email)->firstOrFail();
            if (Carbon::now()->lessThan($user->otp_expires_at)) {
                return $this->returnData('error', 'OTP Not expired');
            } elseif (Carbon::now()->isAfter($user->otp_expires_at) || $user->otp_expires_at == null) {
                $verificationCode = rand(100000, 999999);
                $user->verify_code = $verificationCode;
                $user->otp_expires_at = Carbon::now()->addMinutes(5);
                $user->save();
                Mail::to($user->email)->send(new OtpMail($verificationCode));
                return $this->returnSuccessMessage('Verification code sent!');
            } else {
                return $this->returnError('You have to try again');
            }
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }
    }
    public function reset_password(Request $request)
    {

        $input = $request->only('email', 'code', 'password', 'password_confirmation');
        $validator = Validator::make($input, [
            'email'     => 'required|email|exists:users,email',
            'password' =>
                'required|string|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|confirmed',
            'code'      => 'required|integer|min_digits:5|max_digits:5',
        ]);

        if ($validator->fails()) {
            return $this->returnValidationError($validator);
        }
        try {

            $user = User::where('email', $request->email)
                ->first();
            if ($user->verify_code == $request['code']) {

                $user->password = $request->password ? Hash::make($request->password) : null;
                $user->save();
                return $this->returnSuccessMessage('Password changed!');
            }
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage());
        }
    }
}
