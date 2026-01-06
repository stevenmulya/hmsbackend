<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained()->onDelete('cascade');
            $table->foreignId('blog_category_id')->constrained()->onDelete('cascade');
            $table->string('title'); // Standard name
            $table->string('slug')->unique();
            $table->string('main_image')->nullable(); // Standard name
            $table->boolean('is_visible')->default(false); // Standard name
            $table->text('description')->nullable(); // Standard name
            $table->longText('content'); // Standard name
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('blogs'); }
};