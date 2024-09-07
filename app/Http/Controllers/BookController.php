<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $books = Cache::remember('books', 60, function () {
                return Book::get();
            });

            return response()->json([
                'httpCode' => 200,
                'status' => true,
                'message' => 'ok',
                'data' => $books
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'httpCode' => 500,
                'status' => false,
                'message' => 'Error when fetching data',
                'data' => []
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //validation 
        $validator = Validator::make($request->all(), [
            'author_id' => 'required',
            'title' => 'required|max:100',
            'description' => 'required',
            'publish_date' => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'httpCode' => 422,
                'status' => false,
                'message' => 'Validation failed',
                'data' => $validator->errors()
            ], 422);
        }

        try {
            // Retrieve the validated input
            $validated = $validator->validated();

            $book = new Book;
            $book->author_id = $validated['author_id'];
            $book->title = $validated['title'];
            $book->description = $validated['description'];
            $book->publish_date = $validated['publish_date'];

            $book->save();

            return response()->json([
                'httpCode' => 201,
                'status' => true,
                'message' => 'Book created successfully!',
                'data' => $book
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'httpCode' => 500,
                'status' => false,
                'message' => 'Error saving book to the database',
                'data' => []
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {

            $book = Cache::remember("book_{$id}", 60, function () use ($id) {
                return Book::where('id', $id)->first();
            });

            if (!$book) {
                return response()->json([
                    'httpCode' => 404,
                    'status' => false,
                    'message' => 'Book not found',
                ], 404);
            }

            return response()->json([
                'httpCode' => 200,
                'status' => true,
                'message' => 'Success',
                'data' => $book
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'httpCode' => 500,
                'status' => false,
                'message' => 'Error fetching Book',
                'data' => []
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //validation 
        $validator = Validator::make($request->all(), [
            'title' => 'max:100',
            'description' => 'max:100',
            'publish_date' => 'date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'httpCode' => 422,
                'status' => false,
                'message' => 'Validation failed',
                'data' => $validator->errors()
            ], 422);
        }

        try {
            $book = Book::find($id);
            if (is_null($book)) {
                return response()->json([
                    'httpCode' => 404,
                    'status' => false,
                    'message' => 'Book Not Found!',
                    'data' => null
                ], 404);
            }

            // Update data book jika ada input yang diberikan
            if ($request->has('title')) {
                $book->title = $request->input('title');
            }
            if ($request->has('description')) {
                $book->description = $request->input('description');
            }
            if ($request->has('publish_date')) {
                $book->publish_date = $request->input('publish_date');
            }

            // Simpan perubahan
            $book->save();

            return response()->json([
                'httpCode' => 200,
                'status' => true,
                'message' => 'Book updated successfully!',
                'data' => $book
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'httpCode' => 500,
                'status' => false,
                'message' => 'Error while update book data',
                'data' => []
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $book = Book::find($id);
            if (is_null($book)) {
                return response()->json([
                    'httpCode' => 404,
                    'status' => false,
                    'message' => 'Book Not Found!',
                    'data' => null
                ], 404);
            }
            $book->delete();

            return response()->json([
                'httpCode' => 200,
                'status' => true,
                'message' => 'Delete successfully!',
                'data' => []
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'httpCode' => 500,
                'status' => false,
                'message' => 'Error while delete book data',
                'data' => []
            ], 500);
        }
    }
}
