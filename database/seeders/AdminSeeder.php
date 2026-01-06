<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash; // <-- Pastikan Hash di-import

class AdminSeeder extends Seeder {
    public function run(): void {
        Admin::firstOrCreate(
            ['admin_username' => 'admin', 'email' => 'admin@hms.com'],
            // Pastikan password di-hash dengan benar
            ['admin_password' => Hash::make('password')]
        );
    }
}