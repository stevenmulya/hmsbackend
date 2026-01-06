<?php
// --- This migration simplifies the product tagging system ---
// It drops the 'tags' and 'product_tag' tables and adds a single 'tags' text column
// to the 'products' table for a simpler, non-relational approach.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the pivot and tags tables
        Schema::dropIfExists('product_tag');
        Schema::dropIfExists('tags');

        // Add a new text column to the products table to store tags
        Schema::table('products', function (Blueprint $table) {
            $table->text('tags')->nullable()->after('product_visibility');
        });
    }

    /**
     * Reverse the migrations.
     * This method will re-create the relational tables and drop the text column.
     */
    public function down(): void
    {
        // Drop the new 'tags' column from the products table
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('tags');
        });

        // Re-create the 'tags' table
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        // Re-create the 'product_tag' pivot table
        Schema::create('product_tag', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('tag_id')->constrained()->onDelete('cascade');
            $table->primary(['product_id', 'tag_id']);
        });
    }
};