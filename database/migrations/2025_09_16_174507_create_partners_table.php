<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('logo_path'); // Stores the file path for the logo
            $table->string('link')->nullable();
            $table->enum('type', ['vendor', 'client']);
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('order')->default(0); // For ordering
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('partners'); }
};