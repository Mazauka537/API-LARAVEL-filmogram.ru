<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Artisan::call('db:seed --class=RoleSeeder --env=testing');
        Artisan::call('db:seed --class=UserSeeder --env=testing');
    }

    public function testRegisterSuccessfully()
    {
        $payload = [
            'name' => 'UserTest',
            'email' => 'user@test.com',
            'password' => 'testpass',
        ];

        $this->json('POST', 'api/register', $payload)
            ->assertStatus(201)
            ->assertJsonStructure([
                'token',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'avatar',
                    'followers',
                    'following',
                    'created_at',
                    'updated_at',
                    'role' => [
                        'id',
                        'name'
                    ]
                ]
            ]);
    }

    public function testRequireNameEmailAndPassword()
    {
        $this->json('POST', 'api/register')
            ->assertStatus(422)
            ->assertJson([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'name' => ['The name field is required.'],
                    'email' => ['The email field is required.'],
                    'password' => ['The password field is required.'],
                ]
            ]);
    }

    public function testInvalidFields()
    {
        $payload = [
            'name' => '',
            'email' => 'user',
            'password' => 'test',
        ];

        $this->json('POST', 'api/register', $payload)
            ->assertStatus(422)
            ->assertJson([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'name' => ['The name field is required.'],
                    'email' => ['The email must be a valid email address.'],
                    'password' => ['The password must be between 6 and 16 characters.'],
                ]
            ]);
    }

    public function testEmailOrNameIsAlreadyExists()
    {
        $payload = [
            'name' => 'test123',
            'email' => 'user123@test.com',
            'password' => 'testtest',
        ];

        $this->json('POST', 'api/register', $payload);

        $this->json('POST', 'api/register', $payload)
            ->assertStatus(406)
            ->assertJson([
                'taken' => ['email', 'name']
            ]);
    }
}
