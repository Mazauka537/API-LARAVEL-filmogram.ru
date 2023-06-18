<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class GetCollectionTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Artisan::call('db:seed --class=CollectionSeeder --env=testing');
    }

    public function testGetCollectionSuccessfully()
    {
        $payload = ['id' => 1];

        $this->json('GET', 'api/get/collection', $payload)
            ->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'title',
                'preview',
                'description',
                'user_id',
                'created_at',
                'updated_at',
                'films' => [
                    '*' => [
                        'id',
                        'collection_id',
                        'film_id',
                        'order',
                        'created_at',
                        'updated_at',
                    ]
                ]
            ]);
    }

    public function testCollectionNotFound()
    {
        $payload = ['id' => 666];

        $this->json('GET', 'api/get/collection', $payload)
            ->assertStatus(404);
    }

    public function testRequiredId()
    {
        $payload = ['id' => ''];

        $this->json('GET', 'api/get/collection', $payload)
            ->assertStatus(422)
            ->assertJson([
                "message" => "The given data was invalid.",
                "errors" => [
                    "id" => ["The id field is required."]
                ]
            ]);
    }

    public function testInvalidIdField()
    {
        $payload = ['id' => 'a'];

        $this->json('GET', 'api/get/collection', $payload)
            ->assertStatus(422)
            ->assertJson([
                "message" => "The given data was invalid.",
                "errors" => [
                    "id" => ["The id must be an integer."]
                ]
            ]);
    }
}
