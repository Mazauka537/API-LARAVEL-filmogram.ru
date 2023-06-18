<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AuthTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Artisan::call('db:seed --class=RoleSeeder --env=testing');
        Artisan::call('db:seed --class=UserSeeder --env=testing');
    }

    public function test_auth_success()
    {
        $user = User::where('name', 'user')->first();
        $token = $user->createToken('greatest_name')->plainTextToken;

        $headers = [
            'Authorization' => 'Bearer ' . $token
        ];

        $response = $this->withHeaders($headers)->getJson('api/auth');

        $response->assertStatus(200)
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

    public function test_auth_denied()
    {
        $response = $this->getJson('api/auth');

        $response->assertStatus(401);
    }
}
