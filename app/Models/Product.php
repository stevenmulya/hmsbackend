<?php
// --- Product Model (Final Version with Text-Based Tags) ---
// This model reflects the database structure where 'tags' is a simple text field.

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are not mass assignable.
     * @var array<int, string>
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be cast.
     * @var array<string, string>
     */
    protected $casts = [
        // --- THE FIX IS HERE: The 'tags' cast has been removed ---
        // 'tags' is no longer treated as an array by Laravel.
        'product_visibility' => 'boolean',
    ];

    /**
     * Get the sub-category that the product belongs to.
     */
    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

    /**
     * Get the gallery images for the product.
     */
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }
}