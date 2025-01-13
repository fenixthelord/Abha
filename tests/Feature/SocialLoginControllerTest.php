<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Socialite;
use Laravel\Sanctum\Sanctum;
use Mockery;
use Tests\TestCase;
class SocialLoginControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful login via social provider.
     */
    public function testSuccessfulSocialLogin()
    {
        // Mock Socialite response
        $socialiteUser = Mockery::mock(\Laravel\Socialite\Two\User::class);
        $socialiteUser->shouldReceive('getId')->andReturn('123456');
        $socialiteUser->shouldReceive('getEmail')->andReturn('testuser@example.com');
        $socialiteUser->shouldReceive('getName')->andReturn('Test User');

        Socialite::shouldReceive('driver->userFromToken')
            ->andReturn($socialiteUser);

        // Create a user and link the social account
        $user = User::factory()->create([
            'email' => 'testuser@example.com',
        ]);

        $user->linkedSocialAccounts()->create([
            'provider_id' => '123456',
            'provider_name' => 'google',
        ]);

        // Perform the request
        $response = $this->postJson('/api/auth/social-login', [
            'provider' => 'google',
            'access_token' => 'fake-token',
        ]);

        // Assert the response
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Logged in successfully.',
        ]);

        // Assert the user is authenticated
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test login with invalid provider.
     */
    public function testSocialLoginInvalidProvider()
    {
        $response = $this->postJson('/api/auth/social-login', [
            'provider' => 'invalid-provider',
            'access_token' => 'fake-token',
        ]);

        $response->assertStatus(422); // Unprocessable Entity
        $response->assertJsonValidationErrors(['provider']);
    }

    /**
     * Test login with invalid access token.
     */
    public function testSocialLoginInvalidAccessToken()
    {
        Socialite::shouldReceive('driver->userFromToken')
            ->andThrow(new \Exception('Invalid token.'));

        $response = $this->postJson('/api/auth/social-login', [
            'provider' => 'google',
            'access_token' => 'invalid-token',
        ]);

        $response->assertStatus(401); // Unauthorized
        $response->assertJson([
            'message' => 'Invalid token.',
        ]);
    }

    /**
     * Test login when no linked account is found.
     */
    public function testSocialLoginNoLinkedAccount()
    {
        // Mock Socialite response
        $socialiteUser = Mockery::mock(\Laravel\Socialite\Two\User::class);
        $socialiteUser->shouldReceive('getId')->andReturn('123456');
        $socialiteUser->shouldReceive('getEmail')->andReturn('unlinkeduser@example.com');

        Socialite::shouldReceive('driver->userFromToken')
            ->andReturn($socialiteUser);

        // Perform the request
        $response = $this->postJson('/api/auth/social-login', [
            'provider' => 'google',
            'access_token' => 'fake-token',
        ]);

        $response->assertStatus(404); // Not Found
        $response->assertJson([
            'message' => 'No account is linked to this social account.',
        ]);
    }

}
