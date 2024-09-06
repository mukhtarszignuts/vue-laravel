<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserRegistrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_validates_registration_input()
    {
        $response = $this->postJson('/api/registration', []);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY) // Expecting validation errors
            ->assertJsonValidationErrors(['name', 'email', 'phone', 'password']);
    }

    /** @test */
    public function it_registers_a_new_user_successfully()
    {
        $response = $this->postJson('/api/registration', [
            'name'      => 'John Doe',
            'email'     => 'john.doe@example.com',
            'phone'     => '1234567890',
            'password'  => 'P@ssw0rd',
            'city'      => 'New York',
        ]);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(['message' => 'Registration successfully..!']);

        $this->assertDatabaseHas('users', [
            'name'  => 'John Doe',
            'email' => 'john.doe@example.com',
            'phone' => '1234567890',
            'city'  => 'New York',
        ]);

        $user = User::where('email', 'john.doe@example.com')->first();
        $this->assertTrue(Hash::check('P@ssw0rd', $user->password));
    }

    /** @test */
    public function it_fails_if_email_is_not_unique()
    {
        User::create([
            'name'     => 'Existing User',
            'email'    => 'existing.user@example.com',
            'phone'    => '0987654321',
            'password' => Hash::make('ExistingP@ssw0rd'),
        ]);

        $response = $this->postJson('/api/registration', [
            'name'      => 'New User',
            'email'     => 'existing.user@example.com',
            'phone'     => '1234567890',
            'password'  => 'NewP@ssw0rd',
            'city'      => 'Los Angeles',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function it_fails_if_password_does_not_meet_criteria()
    {
        $response = $this->postJson('/api/registration', [
            'name'      => 'John Doe',
            'email'     => 'john.doe@example.com',
            'phone'     => '1234567890',
            'password'  => 'short',
            'city'      => 'New York',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['password']);
    }
}
