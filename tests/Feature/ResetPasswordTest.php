<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\PasswordResetToken;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ResetPasswordTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_resets_the_password_successfully()
    {
        // Arrange
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword123'),
        ]);

        $token = Str::random(60);
        PasswordResetToken::create([
            'email' => $user->email,
            'token' => $token,
        ]);

        // Act
        $response = $this->postJson('/api/reset-password', [
            'token'            => $token,
            'email'            => $user->email,
            'password'         => 'newpassword123',
            'confirm_password' => 'newpassword123',
        ]);

        // Assert
        $response->assertStatus(200);
        // $response->assertJson([
        //     'status'  => 200,
        //     'message' => 'Password reset successfully',
        //     'data'    => []
        // ]);

        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));

        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => $user->email,
            'token' => $token,
        ]);
    }

    /** @test */
    public function it_fails_if_the_token_is_invalid()
    {
        // Arrange
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword123'),
        ]);

        // Act
        $response = $this->postJson('/api/reset-password', [
            'token'            => 'invalidtoken',
            'email'            => $user->email,
            'password'         => 'newpassword123',
            'confirm_password' => 'newpassword123',
        ]);

        // Assert
        $response->assertStatus(404);
        // $response->assertJsonValidationErrors(['token']);
    }

    /** @test */
    public function it_fails_if_passwords_do_not_match()
    {
        // Arrange
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword123'),
        ]);

        $token = Str::random(60);
        PasswordResetToken::create([
            'email' => $user->email,
            'token' => $token,
        ]);

        // Act
        $response = $this->postJson('/api/reset-password', [
            'token'            => $token,
            'email'            => $user->email,
            'password'         => 'newpassword123',
            'confirm_password' => 'differentpassword123',
        ]);

        // Assert
        $response->assertStatus(422);
        // $response->assertJsonValidationErrors(['confirm_password']);
    }

    /** @test */
    public function it_fails_if_email_does_not_exist()
    {
        // Arrange
        $token = Str::random(60);
        PasswordResetToken::create([
            'email' => 'nonexistent@example.com',
            'token' => $token,
        ]);

        // Act
        $response = $this->postJson('/api/reset-password', [
            'token'            => $token,
            'email'            => 'nonexistent@example.com',
            'password'         => 'newpassword123',
            'confirm_password' => 'newpassword123',
        ]);

        // Assert
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }
}
