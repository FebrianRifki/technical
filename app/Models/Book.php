<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @OA\Schema(
 *     schema="Book",
 *     type="object",
 *     required={"author_id", "title", "description", "publish_date"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="author_id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Book Title"),
 *     @OA\Property(property="description", type="string", example="Book description"),
 *     @OA\Property(property="publish_date", type="string", format="date", example="2024-01-01")
 * )
 */

class Book extends Model
{
    use HasFactory;

    public $timestamps = false;

    /**
     * Get the user that owns the Book
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }
}
