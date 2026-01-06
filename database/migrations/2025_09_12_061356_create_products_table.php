<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_category_id')->constrained()->onDelete('cascade');
            $table->string('product_name');
            $table->string('slug')->unique();
            $table->string('product_code')->unique();
            $table->json('product_similaritytags')->nullable();
            $table->text('product_description')->nullable();
            $table->string('product_size')->nullable();
            $table->decimal('product_weight', 8, 2)->nullable();
            $table->string('product_mainimage')->nullable();
            $table->json('product_imagelist')->nullable();
            $table->decimal('product_price', 15, 2);
            $table->boolean('product_visibility')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('products'); }
};