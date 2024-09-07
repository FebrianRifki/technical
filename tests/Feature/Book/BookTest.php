<?php

use App\Models\Book;
use App\Models\Author;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('Get list book', function () {
    $books = Book::factory()->count(5)->create();

    $response = $this->get('/api/books');
    $response->assertStatus(200)
        ->assertJsonFragment([
            'author_id' => $books->first()->author_id,
            'title' => $books->first()->title,
            'description' => $books->first()->description,
            'publish_date' => $books->first()->publish_date
        ])
        ->assertJsonStructure([
            'httpCode',
            'status',
            'message',
            'data' => [
                '*' => [
                    'id',
                    'author_id',
                    'title',
                    'description',
                    'publish_date'
                ],
            ]
        ]);
});

test('Get book data by id', function () {
    $book = Book::factory()->create();

    $response = $this->get("/api/books/{$book->id}");

    $response->assertStatus(200)
        ->assertJsonFragment([
            'author_id' => $book->author_id,
            'title' => $book->title,
            'description' => $book->description,
            'publish_date' => $book->publish_date
        ])
        ->assertJsonStructure([
            'httpCode',
            'status',
            'message',
            'data' => [
                'id',
                'title',
                'description',
                'publish_date'
            ],
        ]);
});

test('create a new Book', function (){
    $author = Author::factory()->create();
    $bookData = [
        'author_id' => $author->id,
        'title' => 'Illo animi',
        'description' => 'new description',
        'publish_date' => '1999-12-12'
    ];

    $response  = $this->postJson('/api/books', $bookData);

    $response->assertStatus(201)
        ->assertJsonFragment([
            'title' => "Illo animi",
            'description' => 'new description',
            'publish_date' => '1999-12-12'
        ])
        ->assertJsonStructure([
            'httpCode',
            'status',
            'message',
            'data' => [
                'id',
                'title',
                'description',
                'publish_date'
            ]
        ]);
    $this->assertDatabaseHas('books', [
        'title' => 'Illo animi'
    ]);
});

test('update Book data', function () {
    $author = Author::factory()->create();
    $book = Book::factory()->create([
        'author_id' => $author->id
    ]);

    $newData = [
        'title' => 'Asyitel Aitum',
        'description' => 'New description',
        'publish_date' => '1999-12-12'
    ];

    $response = $this->putJson("/api/books/{$book->id}", $newData);

    $response->assertStatus(200)
        ->assertJsonFragment([
            'title' => 'Asyitel Aitum',
            'description' => 'New description',
            'publish_date' => '1999-12-12'
        ])
        ->assertJsonStructure([
            'httpCode',
            'status',
            'message',
            'data' => [
                'author_id',
                'id',
                'title',
                'description',
                'publish_date'
            ]
        ]);

    $this->assertDatabaseHas('books', [
        'id' => $book->id,
        'title' => 'Asyitel Aitum',
        'description' => 'New description',
        'publish_date' => '1999-12-12'
    ]);
});

test('delete Book data', function(){
    $book = Book::factory()->create();

    $response = $this->delete("api/books/{$book->id}");

    $response->assertStatus(200)
    ->assertJsonStructure([
        'httpCode',
        'status',
        'message',
        'data' => []
    ]);

    $this->assertDatabaseMissing('books', [
        'id' => $book->id
    ]);
});
