<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase, WithFaker;

//    protected $user;
    public function test_a_successful_signup(): void
    {
        $user = [
            "first_name" => fake()->firstName,
            "last_name" => fake()->lastName,
            "display_name" => fake()->userName,
            "email" => fake()->unique()->safeEmail,
            "password" => "password123",
            "password_confirmation" => "password123",
        ];

        $response = $this->postJson(route("signup"), $user);

        $response->assertCreated()
            ->assertJsonStructure([
                "data" => ["access_token"],
                "message",
            ]);
    }


    public function test_a_successful_manual_login(): void
    {
        $user = User::factory()->create([
            "handle" => "Test",
            "password" => bcrypt("123456seed")
        ]);

//        $this->user = $user;

        $response = $this->postJson(route("login"), [
            "username" => "test",
            "password" => "123456seed"
        ]);


        $response->assertOk();
        $response->assertJsonStructure(["data" => ["access_token"]]);
    }

    public function test_duplicate_email_and_handle(): void
    {
        $existingUser = User::factory()->create();
        $user = User::factory()->make([
            'display_name' => $existingUser->display_name,
            'email' => $existingUser->email,
        ])->toArray();
        $user['password'] = 'password123';
        $user['password_confirmation'] = 'password123';

        $response = $this->postJson(route("signup"), $user);

        $response->assertBadRequest()
            ->assertJsonStructure([
                "error",
                "message",
            ])
            ->assertJsonValidationErrors(['display_name', 'email']);
    }

    public function test_a_failed_manual_login(): void
    {
        $response = $this->postJson(route("login"), [
            "username" => "tester",
            "password" => "123456eed"
        ]);

        $response->assertNotFound();
    }


}
