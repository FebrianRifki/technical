<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class AuthorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        try {
            $authors = Cache::remember('authors', 60, function () {
                return Author::get();
            });

            return response()->json([
                'httpCode' => 200,
                'status' => true,
                'message' => 'ok',
                'data' => $authors
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
            'name' => 'required|max:100',
            'bio' => 'required',
            'birth_date' => 'required|date'
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

            $author = new Author;
            $author->name = $validated['name'];
            $author->bio = $validated['bio'];
            $author->birth_date = $validated['birth_date'];

            $author->save();

            return response()->json([
                'httpCode' => 200,
                'status' => true,
                'message' => 'Author created successfully!',
                'data' => $author
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'httpCode' => 500,
                'status' => false,
                'message' => 'Error saving author to the database',
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

            $author = Cache::remember("author_{$id}", 60, function () use ($id) {
                return Author::where('id', $id)->first();
            });

            if (!$author) {
                return response()->json([
                    'httpCode' => 404,
                    'status' => false,
                    'message' => 'Author not found',
                ], 404);
            }

            return response()->json([
                'httpCode' => 200,
                'status' => true,
                'message' => 'Success',
                'data' => $author
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'httpCode' => 500,
                'status' => false,
                'message' => 'Error fetching author',
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
            'name' => 'max:100',
            'bio' => 'max:100',
            'birth_date' => 'date'
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
            $author = Author::find($id);
            if (is_null($author)) {
                return response()->json([
                    'httpCode' => 404,
                    'status' => false,
                    'message' => 'Author Not Found!',
                    'data' => null
                ], 404);
            }

            // Update data author jika ada input yang diberikan
            if ($request->has('name')) {
                $author->name = $request->input('name');
            }
            if ($request->has('bio')) {
                $author->bio = $request->input('bio');
            }
            if ($request->has('birth_date')) {
                $author->birth_date = $request->input('birth_date');
            }

            // Simpan perubahan
            $author->save();

            return response()->json([
                'httpCode' => 200,
                'status' => true,
                'message' => 'Author updated successfully!',
                'data' => $author
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'httpCode' => 500,
                'status' => false,
                'message' => 'Error while update author data',
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
            $author = Author::find($id);
            if (is_null($author)) {
                return response()->json([
                    'httpCode' => 404,
                    'status' => false,
                    'message' => 'Author Not Found!',
                    'data' => null
                ], 404);
            }
            $author->delete();

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
                'message' => 'Error while delete author data',
                'data' => []
            ], 500);
        }
    }

    /**
     * display assosated data.
     */
    public function getBooksByAuthor(string $id)
    {
        $data = Book::join('authors', 'books.author_id', '=', 'authors.id')
            ->select('books.*', 'authors.name as author_name')
            ->where('books.author_id', $id)
            ->get();

        return response()->json([
            'httpCode' => 200,
            'status' => true,
            'message' => 'Books retrieved successfully',
            'data' => $data
        ], 200);
    }
}
