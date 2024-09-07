<?php

use App\Models\Author;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);


test('Get list author', function () {
    $authors = Author::factory()->count(5)->create();

    $response = $this->get('/api/authors');

    $response->assertStatus(200)
        ->assertJsonFragment([
            'name' => $authors->first()->name
        ])
        ->assertJsonStructure([
            'httpCode',
            'status',
            'message',
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'bio',
                    'birth_date'
                ],
            ]
        ]);
});
