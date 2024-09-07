<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     version="1.0",
 *     title="API Documentation",
 *     description="Documentation for Author and Book API",
 *     @OA\Contact(name="Swagger API Team")
 * )
 */

class AuthorController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/authors",
     *     summary="Get all authors",
     *     tags={"Authors"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="httpCode", type="integer", example=200),
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="ok"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Author")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     * 
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
     * @OA\Post(
     *     path="/api/authors",
     *     summary="Create a new author",
     *     tags={"Authors"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "bio", "birth_date"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="bio", type="string", example="Author biography"),
     *             @OA\Property(property="birth_date", type="string", format="date", example="1980-01-01")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Author created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="httpCode", type="integer", example=201),
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Author created successfully!"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Author"
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
     *             @OA\Property(property="message", type="string", example="Error saving author to the database"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
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
                'httpCode' => 201,
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
     * @OA\Get(
     *     path="/api/authors/{id}",
     *     summary="Get a specific author",
     *     tags={"Authors"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Author ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Author retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="httpCode", type="integer", example=200),
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Author"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Author not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="httpCode", type="integer", example=404),
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Author not found"),
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
     *             @OA\Property(property="message", type="string", example="Error fetching author"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
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
     * @OA\Put(
     *     path="/api/authors/{id}",
     *     summary="Update an existing author",
     *     tags={"Authors"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Author ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John Doe Updated"),
     *             @OA\Property(property="bio", type="string", example="Updated biography"),
     *             @OA\Property(property="birth_date", type="string", format="date", example="1981-01-01")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Author updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="httpCode", type="integer", example=200),
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Author updated successfully!"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Author"
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
     *         description="Author not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="httpCode", type="integer", example=404),
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Author Not Found!"),
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
     *             @OA\Property(property="message", type="string", example="Error while update author data"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
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
     * @OA\Delete(
     *     path="/api/authors/{id}",
     *     summary="Delete a specific author",
     *     tags={"Authors"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Author ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Author deleted successfully",
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
     *         description="Author not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="httpCode", type="integer", example=404),
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Author Not Found!"),
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
     *             @OA\Property(property="message", type="string", example="Error while delete author data"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
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
     * @OA\Get(
     *     path="/api/authors/{id}/books",
     *     summary="Get books by a specific author",
     *     tags={"Authors"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Author ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Books retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="httpCode", type="integer", example=200),
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Books retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="title", type="string"),
     *                     @OA\Property(property="author_id", type="integer"),
     *                     @OA\Property(property="published_date", type="string", format="date"),
     *                     @OA\Property(property="author_name", type="string")
     *                 )
     *             )
     *         )
     *     )
     * )
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
