<?php

use App\Http\Controllers\AuthorController;
use App\Http\Controllers\BookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 *@OA\Info(
 *version="1.0",
 *title="Example API",
 *description="Example info",
 *@OA\Contact(name="Swagger API Team")
 *)
 */

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('authors', AuthorController::class);
Route::get('authors/{id}/books', [AuthorController::class, 'getBooksByAuthor']);

Route::apiResource('books', BookController::class);
