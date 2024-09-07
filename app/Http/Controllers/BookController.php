<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

class BookController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/books",
     *     summary="Get a list of books",
     *     tags={"Books"},
     *     @OA\Response(
     *         response=200,
     *         description="Books retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="httpCode", type="integer", example=200),
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="ok"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="author_id", type="integer"),
     *                     @OA\Property(property="title", type="string"),
     *                     @OA\Property(property="description", type="string"),
     *                     @OA\Property(property="publish_date", type="string", format="date")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="httpCode", type="integer", example=500),
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error when fetching data"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
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
     * @OA\Post(
     *     path="/api/books",
     *     summary="Create a new book",
     *     tags={"Books"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"author_id", "title", "description", "publish_date"},
     *             @OA\Property(property="author_id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="Book Title"),
     *             @OA\Property(property="description", type="string", example="Book description"),
     *             @OA\Property(property="publish_date", type="string", format="date", example="2024-01-01")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Book created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="httpCode", type="integer", example=201),
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Book created successfully!"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Book"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="httpCode", type="integer", example=422),
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="data", type="object", additionalProperties=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="httpCode", type="integer", example=500),
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error saving book to the database"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
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
     * @OA\Get(
     *     path="/api/books/{id}",
     *     summary="Get a specific book",
     *     tags={"Books"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Book ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Book retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="httpCode", type="integer", example=200),
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Book"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Book not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="httpCode", type="integer", example=404),
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Book not found"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="httpCode", type="integer", example=500),
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error fetching Book"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
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
     * @OA\Put(
     *     path="/api/books/{id}",
     *     summary="Update an existing book",
     *     tags={"Books"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Book ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Updated Book Title"),
     *             @OA\Property(property="description", type="string", example="Updated description"),
     *             @OA\Property(property="publish_date", type="string", format="date", example="2024-02-01")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Book updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="httpCode", type="integer", example=200),
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Book updated successfully!"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Book"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="httpCode", type="integer", example=422),
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="data", type="object", additionalProperties=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Book not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="httpCode", type="integer", example=404),
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Book Not Found!"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="httpCode", type="integer", example=500),
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error while update book data"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
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
     * @OA\Delete(
     *     path="/api/books/{id}",
     *     summary="Delete a specific book",
     *     tags={"Books"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Book ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Book deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="httpCode", type="integer", example=200),
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Delete successfully!"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Book not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="httpCode", type="integer", example=404),
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Book Not Found!"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="httpCode", type="integer", example=500),
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error while delete book data"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
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
