<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ForgotPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_sends_a_password_reset_link()
    {
        // Arrange: Create a user
        $user = User::factory()->create([
            'email' => 'testuser@example.com',
        ]);

        // Mock notification sending
        Notification::fake();

        // Act: Send a request to forgotPassword endpoint
        $response = $this->postJson('/api/forget-password', [
            'email' => 'testuser@example.com',
        ]);

        // Assert: Check that the response is as expected
        $response->assertOk();
        $response->assertJson(['message' => 'Password reset link sent successfully']);

        // Assert: Check if a PasswordResetToken was created
        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => 'testuser@example.com',
        ]);

        // Assert: Check that the notification was sent
        Notification::assertSentTo(
            $user,
            ForgotPasswordNotification::class,
            function ($notification) use ($user) {
                return $notification->email === $user->email;
            }
        );
    }

    /** @test */
    public function it_fails_when_email_does_not_exist()
    {
        // Act: Send a request with a non-existent email
        $response = $this->postJson('/api/forget-password', [
            'email' => 'nonexistentuser@example.com',
        ]);

        // Assert: Check that the response status is 404
        $response->assertStatus(404);

        // Assert: Check that no PasswordResetToken was created
        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => 'nonexistentuser@example.com',
        ]);
    }
}
