<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_user_can_logout()
    {
        // Create a user
        $user = User::factory()->create();

        // Create an access token for the user
        Sanctum::actingAs($user);

        // Make a request to the logout route
        $response = $this->postJson('/api/logout');

        // Assert that the response status is OK
        $response->assertStatus(200);

        // Assert the success message
        $response->assertJson([
            'message' =>'Sucessfully logged out'
        ]);

        // Assert that the token was deleted
        $this->assertCount(0, $user->tokens);
    }
}
