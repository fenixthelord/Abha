<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Models\LinkedSocialAccount;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Two\User as ProviderUser;

class SocialLoginController extends Controller
{

    use ResponseTrait;

    public function login(Request $request)
    {
        DB::beginTransaction();
        $messages = [
            'provider.in' => 'Only Google, Facebook, Apple, Stripe, or Firebase are allowed.',
        ];
        $validator = Validator::make(
            $request->all(),
            [
                'provider' => 'required|in:google,facebook,apple,stripe,firebase',
                'access_token' => 'required|string',
            ],
            $messages
        );

        if ($validator->fails()) {
            return $this->returnValidationError($validator);
        }

        try {
            $accessToken = $request->get('access_token');
            $provider = $request->get('provider');
            $providerUser = Socialite::driver($provider)->userFromToken($accessToken);
        } catch (Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 401);
        }

        if (filled($providerUser)) {
            $user = $this->findUserBySocialAccount($providerUser, $provider);
        } else {
            return $this->error(
                message: 'Social provider authentication failed.',
                code: 401
            );
        }

        if ($user) {
            auth()->login($user);
            DB::commit();
            return response()->json([
                'message' => 'Logged in successfully.',
                'data' => ['token' => auth()->user()->createToken('API Token')->plainTextToken],
            ]);
        } else {
            DB::rollBack();
            return $this->error(
                message: 'No account is linked to this social account.',
                code: 404
            );
        }
    }

    public function linkSocialAccount(Request $request)
    {
        DB::beginTransaction();
        $messages = [
            'provider.in' => 'Only Google, Facebook, Apple, Stripe, or Firebase are allowed.',
        ];
        $validator = Validator::make(
            $request->all(),
            [
                'provider' => 'required|in:google,facebook,apple,stripe,firebase',
                'access_token' => 'required|string',
            ],
            $messages
        );

        if ($validator->fails()) {
            return $this->returnValidationError($validator);
        }

        if (!auth()->check()) {
            return $this->error(
                message: 'You must be logged in to link a social account.',
                code: 401
            );
        }

        try {
            $accessToken = $request->get('access_token');
            $provider = $request->get('provider');
            $providerUser = Socialite::driver($provider)->userFromToken($accessToken);
        } catch (Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 401);
        }

        $user = auth()->user();

        // Check if the social account is already linked
        $existingSocialAccount = LinkedSocialAccount::query()
            ->where('provider_name', $provider)
            ->where('provider_id', $providerUser->getId())
            ->first();

        if ($existingSocialAccount) {
            return $this->error(
                message: 'This social account is already linked to another user.',
                code: 400
            );
        }

        // Link the social account to the authenticated user
        $user->linkedSocialAccounts()->create([
            'provider_id' => $providerUser->getId(),
            'provider_name' => $provider,
        ]);
        DB::commit();
        return response()->json([
            'message' => 'Social account linked successfully.',
        ]);
    }

    protected function findUserBySocialAccount(ProviderUser $providerUser, string $provider): ?User
    {
        // Find the social account linked to an existing user
        $linkedSocialAccount = LinkedSocialAccount::query()
            ->where('provider_name', $provider)
            ->where('provider_id', $providerUser->getId())
            ->first();

        return $linkedSocialAccount ? $linkedSocialAccount->user : null;
    }
}
