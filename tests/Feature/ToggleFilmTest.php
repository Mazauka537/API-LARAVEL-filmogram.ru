<?php

namespace Tests\Feature;

use App\Models\Film;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class ToggleFilmTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Artisan::call('db:seed --class=UserSeeder --env=testing');
        Artisan::call('db:seed --class=CollectionSeeder --env=testing');
    }

    private function getAuthHeader()
    {
        $user = User::where('name', 'user')->first();
        $token = $user->createToken('greatest_name')->plainTextToken;

        return [
            'Authorization' => 'Bearer ' . $token
        ];
    }


    public function testNotAuthorized() {
        $payload = [
            'collectionId' => 1,
            'filmId' => 42,
        ];

        $this->json('POST', 'api/toggle/film', $payload)
            ->assertStatus(401);
    }

    public function testCollectionIsNotBelongsToUser() {
        $payload = [
            'collectionId' => 2,
            'filmId' => 42,
        ];

        $this->withHeaders($this->getAuthHeader())->json('POST', 'api/toggle/film', $payload)
            ->assertStatus(403);
    }

    public function testFilmAddedToCollection() {
        $payload = [
            'collectionId' => 1,
            'filmId' => 42,
        ];

        $this->withHeaders($this->getAuthHeader())->json('POST', 'api/toggle/film', $payload)
            ->assertStatus(201)
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

    public function testFilmDeletedFromCollection() {
        Artisan::call('db:seed --class=FilmSeeder --env=testing');

        $payload = [
            'collectionId' => 1,
            'filmId' => 24333,
        ];

        $response = $this->withHeaders($this->getAuthHeader())->json('POST', 'api/toggle/film', $payload);

        Log::info($response->getContent());

        $response->assertStatus(200)
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

    public function testOrderOfAddedFilm() {
        $payload = [
            'collectionId' => 1,
            'filmId' => 43,
        ];

        $this->withHeaders($this->getAuthHeader())->json('POST', 'api/toggle/film', $payload)
            ->assertStatus(201);

        $payload['filmId'] = 513;
        $this->withHeaders($this->getAuthHeader())->json('POST', 'api/toggle/film', $payload)
            ->assertStatus(201);

        $films = Film::where('collection_id', $payload['collectionId'])->get();

        $this->assertEquals(1000, $films[0]->order);
        $this->assertEquals(2000, $films[1]->order);
    }

    public function testInvalidFields() {
        $payload = [
            'collectionId' => 'a',
            'filmId' => 'd',
        ];

        $this->withHeaders($this->getAuthHeader())->json('POST', 'api/toggle/film', $payload)
            ->assertStatus(422)
            ->assertJson([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'collectionId' => ['The collection id must be an integer.'],
                    'filmId' => ['The film id must be an integer.'],
                ]
            ]);
    }
}
