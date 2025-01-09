<?php

namespace App\Http\Controllers;

use App\Http\Traits\FileUploader;
use App\Http\Traits\GeneralTrait;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;



class UserAuthController extends Controller
{
    use GeneralTrait;
    use FileUploader;

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email|unique:users,email,',
            'password' => 'required|min:8',
            'phone' => 'required|unique:users,phone|numeric',
            'gender' => 'required|in:male,female',
            'alt' => 'string'
        ]);

        if ($validator->fails()) {
            return $this->requiredField($validator->errors()->first());
        }
        try {
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => $request->password ? Hash::make($request->password) : null,
                'role' => 'user',
                'alt' => $request->alt,
                'gender' => $request->gender,
            ]);
            return $this->apiResponse('succses');

        } catch (\Exception $ex) {
            return $this->apiResponse(null, false, $ex->getMessage(), 500);
        }
    }


    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user' => 'required',
                'password' => 'required',]);
            if ($validator->fails()) {
                return $this->requiredField($validator->errors()->first());
            }
            $username = filter_var($request->user, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
            $user = User::where($username, $request->user)->first();
            if (!$user || !Hash::check($request->password, $user->password)) {
                return $this->apiResponse(null, false, 'email or phone or password false', 401);
            } else {
                $data['user'] = $user;
                $data['token'] = $user->createToken('MyApp')->plainTextToken;
                return $this->apiResponse($data, true, null, 200);
            }
        }

        catch
            (\Exception $ex) {
                return $this->apiResponse(null, false, $ex->getMessage(), 500);
            }
    }
    public function addImage(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);
            if ($validator->fails()) {
                return $this->requiredField($validator->errors()->first());
            }
            $data=auth()->user();
            $image=$this->uploadImagePublic($request,$data,$request->type);
            return $this->apiResponse($image);
        }
        catch (\Exception $ex)
        {
            return $this->apiResponse(null,false,$ex->getMessage(),401);
        }
    }



    public function logout(Request $request)
    {
        try {
        auth()->user()->tokens()->delete();
        return $this->apiResponse("logged out");
        }
        catch (\Exception $ex)
        {
            return $this->apiResponse(null, false, $ex->getMessage(), $ex->getCode());
        }
    }
}

