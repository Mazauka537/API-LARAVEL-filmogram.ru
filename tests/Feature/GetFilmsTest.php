<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class GetFilmsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Artisan::call('db:seed --class=CollectionSeeder --env=testing');
        Artisan::call('db:seed --class=FilmSeeder --env=testing');
    }

    public function testGetFilmsSuccessfully() {
        $payload = [
            'collectionId' => 1,
            'page' => 1
        ];

        $response = $this->json('GET', 'api/get/films', $payload);

        Log::info($response->getContent());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'totalPages',
                'films' => [
                    '*' => [
                        'id',
                        'collection_id',
                        'film_id',
                        'order',
                        'created_at',
                        'updated_at',
                        'filmInfo' => [
                            '*' => []
                        ]
                    ]
                ]
            ]);
    }

    public function testInvalidField() {
        $payload = [
            'collectionId' => 'h',
            'page' => 0
        ];

        $this->json('GET', 'api/get/films', $payload)
            ->assertStatus(422);
    }

    public function testRequireFilmsIds() {

        $this->json('GET', 'api/get/films')
            ->assertStatus(422);
    }
}
