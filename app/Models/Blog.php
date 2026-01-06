<?php
// --- Blog Model (Final Version) ---
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    // Ensure the cast uses the correct column name 'is_visible'
    protected $casts = [
        'is_visible' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function author()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function images()
    {
        return $this->hasMany(BlogImage::class);
    }
}