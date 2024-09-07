<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @OA\Schema(
 *     schema="Author",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="bio", type="string", example="Author bio"),
 *     @OA\Property(property="birth_date", type="string", format="date", example="1980-01-01")
 * )
 */

class Author extends Model
{
    use HasFactory;

    public $timestamps = false;

    /**
     * Get all of the books for the Author
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */

    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }
}
