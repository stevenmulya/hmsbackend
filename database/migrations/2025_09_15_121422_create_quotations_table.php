<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['on progress', 'done'])->default('on progress');
            $table->boolean('is_seen_by_admin')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps(); // Handles date and hour automatically
        });
    }
    public function down(): void { Schema::dropIfExists('quotations'); }
};