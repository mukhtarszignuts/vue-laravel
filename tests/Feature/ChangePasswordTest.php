<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ChangePasswordTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful password change.
     *
     * @return void
     */
    public function test_user_can_change_password_successfully()
    {
        // Create a user with a known password
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword123'),
        ]);

        // Authenticate the user
        $this->actingAs($user);

        // Make a request to change the password
        $response = $this->postJson('/api/change-password', [
            'current_password' => 'oldpassword123',
            'password' => 'newpassword123',
            'confirm_password' => 'newpassword123',
        ]);

        // Assert that the response status is OK
        $response->assertStatus(200);

        // Assert the success message
        $response->assertJson([
            'message' => 'Password changes successfully',
        ]);

        // Assert that the password has been changed
        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
    }

    /**
     * Test password change with incorrect current password.
     *
     * @return void
     */
    public function test_user_cannot_change_password_with_incorrect_current_password()
    {
        // Create a user with a known password
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword123'),
        ]);

        // Authenticate the user
        $this->actingAs($user);

        // Make a request to change the password with an incorrect current password
        $response = $this->postJson('/api/change-password', [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword123',
            'confirm_password' => 'newpassword123',
        ]);

        // Assert that the response status is unauthorized
        $response->assertStatus(400);

        // Assert the error message
        $response->assertJson([
            'message' => 'The current password is incorrect',
        ]);
    }

    /**
     * Test password change with invalid input.
     *
     * @return void
     */
    public function test_user_cannot_change_password_with_invalid_input()
    {
        // Create a user with a known password
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword123'),
        ]);

        // Authenticate the user
        $this->actingAs($user);

        // Make a request to change the password with invalid input
        $response = $this->postJson('/api/change-password', [
            'current_password' => 'oldpassword123',
            'password' => 'short',
            'confirm_password' => 'different',
        ]);

        // Assert that the response status is validation error (422)
        $response->assertStatus(422);

        // Assert that the response contains validation errors
        $response->assertJsonValidationErrors([
            'password',
            'confirm_password',
        ]);
    }
}
