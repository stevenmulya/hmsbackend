<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany; // <-- 1. Import the HasMany class
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;
    
    protected $guarded = ['id'];
    
    protected $hidden = [
        'customer_password',
        'remember_token',
        'verification_code',
        'password_reset_code'
    ];
    
    protected $casts = [
        'customer_password' => 'hashed',
        'phone_verified_at' => 'datetime',
        'verification_code_expires_at' => 'datetime',
        'password_reset_code_expires_at' => 'datetime',
        'transaction_history' => 'array',
    ];

    /**
     * --- THE FIX IS HERE ---
     * Defines the one-to-many relationship between a customer and their quotations.
     * A customer can have many quotations.
     * (Mendefinisikan relasi one-to-many antara customer dan penawarannya)
     */
    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }
}