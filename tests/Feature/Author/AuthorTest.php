<?php

use App\Models\Author;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);


test('Get list author', function () {
    $authors = Author::factory()->count(5)->create();

    $response = $this->get('/api/authors');

    $response->assertStatus(200)
        ->assertJsonFragment([
            'name' => $authors->first()->name,
            'bio' => $authors->first()->bio,
            'birth_date' => $authors->first()->birth_date
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


test('Get author data by id', function () {
    $author = Author::factory()->create();

    $response = $this->get("/api/authors/{$author->id}");

    $response->assertStatus(200)
        ->assertJsonFragment([
            'name' => $author->name,
            'bio' => $author->bio,
            'birth_date' => $author->birth_date
        ])
        ->assertJsonStructure([
            'httpCode',
            'status',
            'message',
            'data' => [
                'id',
                'name',
                'bio',
                'birth_date'
            ],
        ]);
});

test('create a new Author', function (){
    $authorData = [
        'name' => 'John Doe',
        'bio' => 'new author',
        'birth_date' => '2000-12-12'
    ];

    $response  = $this->postJson('/api/authors', $authorData);

    $response->assertStatus(201)
        ->assertJsonFragment([
            'name' => "John Doe",
            'bio' => 'new author',
            'birth_date' => '2000-12-12'
        ])
        ->assertJsonStructure([
            'httpCode',
            'status',
            'message',
            'data' => [
                'id',
                'name',
                'bio',
                'birth_date'
            ]
        ]);
    $this->assertDatabaseHas('authors', [
        'name' => 'John Doe'
    ]);
});

test('update Author data', function(){
    $author = Author::factory()->create();

    $newData = [
        'name' => 'Asyitel Aitum',
        'bio' => 'New bio',
        'birth_date' => '1999-12-12'
    ];

    $response = $this->putJson("api/authors/{$author->id}", $newData);

    $response->assertStatus(200)
        ->assertJsonFragment([
           'name' => 'Asyitel Aitum',
            'bio' => 'New bio',
            'birth_date' => '1999-12-12'
        ])
        ->assertJsonStructure([
            'httpCode',
            'status',
            'message',
            'data' => [
                'id',
                'name',
                'bio',
                'birth_date'
            ]
        ]);
        $this->assertDatabaseHas('authors', [
            'name' => 'Asyitel Aitum',
            'bio' => 'New bio',
            'birth_date' => '1999-12-12'
        ]);
});

test('delete Author data', function(){
    $author = Author::factory()->create();

    $response = $this->delete("api/authors/{$author->id}");

    $response->assertStatus(200)
    ->assertJsonStructure([
        'httpCode',
        'status',
        'message',
        'data' => []
    ]);

    $this->assertDatabaseMissing('authors', [
        'id' => $author->id
    ]);
});

