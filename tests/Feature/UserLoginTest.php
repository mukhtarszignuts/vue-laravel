<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserLoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_login_with_valid_credentials()
    {
        // Arrange
        $user = User::factory()->create([
            'password' => Hash::make('password123'), // Assuming you have a User factory
        ]);

        // Act
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        // Assert
        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'message' =>'Logged in successfully',
            ]);
           
    }

    /** @test */
    public function login_fails_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'), // Assuming you have a User factory
        ]);
        // Act
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ]);

        // Assert
        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR)
            ->assertJson([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Invalid Credentials',
                'data' => [],
            ]);
    }

    /** @test */
    public function login_fails_if_user_does_not_have_company()
    {
        // Arrange
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
            'role' => 'M',
            'company_id' => null,
        ]);

        // Act
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        // Assert
        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR)
            ->assertJson([
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => "You don't have Company Plese Contect to Admin",
                'data' => []
            ]);
    }
}
