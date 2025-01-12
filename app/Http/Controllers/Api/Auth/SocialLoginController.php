<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Models\LinkedSocialAccount;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Two\User as ProviderUser;

class SocialLoginController extends Controller
{
    use ResponseTrait;

    public function login(Request $request)
    {
        $messages = ['provider.in'=> 'Google Facebook Apple Stripe Firebase only allowed'];
        $validator = Validator::make($request->all(), [
            'provider' => 'required|in:google,facebook,apple,stripe,firebase',
            'access_token' => 'required|string',
        ],
            $messages
        );

        if ($validator->fails()) {
            return $this->returnValidationError($validator, $validator->errors(), $validator->errors());
        }

        try {
            $accessToken = $request->get('access_token');
            $provider = $request->get('provider');
            $providerUser = Socialite::driver($provider)->userFromToken($accessToken);

        } catch (Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ]);
        }

        if (filled($providerUser)) {
            $user = $this->findOrCreate($providerUser, $provider);
        } else {
            $user = $providerUser;
        }
        auth()->login($user);
        if (auth()->check()) {
            return response()->json([
                'message' => 'Logged in successfully',
                'data' => ['token' => auth()->user()->createToken('API Token')->plainTextToken],
            ]);
        } else {
            return $this->error(
                message: 'Failed to Login try again',
                code: 401
            );
        }
    }


    protected function findOrCreate(ProviderUser $providerUser, string $provider): User
    {
        $linkedSocialAccount = LinkedSocialAccount::query()->where('provider_name', $provider)
            ->where('provider_id', $providerUser->getId())
            ->first();

        if ($linkedSocialAccount) {
            return $linkedSocialAccount->user;
        } else {
            $user = null;

            if ($email = $providerUser->getEmail()) {
                $user = User::query()->where('email', $email)->first();
            }

            if (!$user) {
                $user = User::query()->create([
                    'name' => $providerUser->getName(),
                    'email' => $providerUser->getEmail(),
                ]);
                $user->markEmailAsVerified();
            }

            $user->linkedSocialAccounts()->create([
                'provider_id' => $providerUser->getId(),
                'provider_name' => $provider,
            ]);
            return $user;
        }
    }
}
