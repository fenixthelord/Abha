<?php

namespace App\Http\Controllers;

use App\Http\Traits\GeneralTrait;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;



class UserAuthController extends Controller
{
    use GeneralTrait;

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,',
            'password' => 'nullable|min:8',
        ]);

        if ($validator->fails()) {
            if ($validator->errors()->has('email')) {
                return $this->apiResponse(null, false, 'Email already exists', 405);
            }
            return $this->requiredField($validator->errors()->first());
        }
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password ? Hash::make($request->password) : null,
            ]);
            return $this->apiResponse('succses');

        } catch (\Exception $ex) {
            return $this->apiResponse(null, false, $ex->getMessage(), 500);
        }
    }


    public function login(Request $request)
    {
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'nullable',

        ]);

        // Check if validation fails and return errors if any
        if ($validator->fails()) {
            return $this->requiredField($validator->errors()->first());
        }

        try {
            // Attempt to find the user by phone number
            $user = User::where('email', $request->input('email'))->first();

            // Verify the phone
            if (!$user) {
                return $this->apiResponse(null, false, 'Invalid email Or Password .', 400);
            }

            if ($request->filled('password')) {
                if (!Hash::check($request->input('password'), $user->password)) {
                    return $this->apiResponse(null, false, 'Invalid email or password.', 400);
                }
            }
            // Generate a token for the user
            $data['user'] = $user;
            $data['token'] = $user->createToken('MyApp')->plainTextToken;


            return $this->apiResponse($data, true, null, 200);
        } catch (\Exception $ex) {
            return $this->apiResponse(null, false, $ex->getMessage(), 500);
        }
    }


    public function logout(Request $request)
    {
        auth()->user()->tokens()->delete();

        return $this->apiResponse("logged out");
    }
}

