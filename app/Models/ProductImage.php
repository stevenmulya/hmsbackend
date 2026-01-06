<?php
// --- ProductImage Model ---
// This model represents a single image in a product's gallery.

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Get the product that owns the image.
     * (One-to-Many inverse relationship)
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}