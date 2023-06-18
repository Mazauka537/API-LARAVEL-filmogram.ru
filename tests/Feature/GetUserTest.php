<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class GetUserTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Artisan::call('db:seed --class=RoleSeeder --env=testing');
        Artisan::call('db:seed --class=UserSeeder --env=testing');
    }

    public function testGetUserSuccessfully() {
        $this->json('GET', 'api/get/user', ['id' => 1])
            ->assertStatus(200)
            ->assertJsonStructure([
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

    public function testUserNotFound() {
        $this->json('GET', 'api/get/user', ['id' => 666])
            ->assertStatus(404);
    }

    public function testInvalidFields() {
        $this->json('GET', 'api/get/user', ['id' => '1a1'])
            ->assertStatus(422);
    }
}
