<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class LoginTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Artisan::call('db:seed --class=RoleSeeder --env=testing');
        Artisan::call('db:seed --class=UserSeeder --env=testing');
    }

    public function testRequireLoginAndPassword()
    {
        $this->json('POST', 'api/login')
            ->assertStatus(422)
            ->assertJson([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'login' => ['The login field is required.'],
                    'password' => ['The password field is required.']
                ]
            ]);

    }

    public function testEmailOrNameNotFound()
    {
        $payload = ['login' => 'user@email.commmmm', 'password' => 'userpass'];

        $this->json('POST', 'api/login', $payload)
            ->assertStatus(404);
    }

    public function testUserLoginWithEmailSuccessfully()
    {
        $payload = ['login' => 'user@mail.ru', 'password' => '123456'];

        $this->json('POST', 'api/login', $payload)
            ->assertStatus(200)
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

    public function testUserLoginWithNameSuccessfully()
    {
        $payload = ['login' => 'user', 'password' => '123456'];

        $this->json('POST', 'api/login', $payload)
            ->assertStatus(200)
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
}
