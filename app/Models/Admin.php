<?php
// --- Admin Model (Final Version with Blog Relationship) ---
// This model defines the structure and relationships for an administrator account.

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany; // <-- 1. Import the HasMany class
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     * Using guarded is a secure alternative to fillable.
     * @var array<int, string>
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be hidden for serialization.
     * @var array<int, string>
     */
    protected $hidden = [
        'admin_password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     * @var array<string, string>
     */
    protected $casts = [
        'admin_password' => 'hashed',
    ];

    /**
     * --- THE ADDITION IS HERE ---
     * Defines the one-to-many relationship between an admin and their blog posts.
     * An admin can be the author of many blogs.
     * (Mendefinisikan relasi one-to-many antara admin dan post blog mereka)
     */
    public function blogs(): HasMany
    {
        return $this->hasMany(Blog::class);
    }
}