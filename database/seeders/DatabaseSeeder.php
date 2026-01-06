<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * This is the main entry point for all database seeding.
     * It calls other seeder classes to populate the database with initial data.
     */
    public function run(): void
    {
        // This line calls the AdminSeeder to create the default admin account.
        $this->call([
            AdminSeeder::class,
            // You can add other seeders here in the future, for example:
            // CustomerSeeder::class,
            // ProductSeeder::class,
        ]);
    }
}