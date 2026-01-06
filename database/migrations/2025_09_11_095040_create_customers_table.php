<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->string('email')->unique();
            $table->string('customer_phone')->unique();
            $table->text('customer_address')->nullable();
            $table->string('customer_username')->unique();
            $table->string('customer_password');
            $table->enum('customer_role', ['company', 'personal'])->default('personal');
            $table->string('company_name')->nullable();
            $table->string('company_role')->nullable();
            $table->string('company_id_npwp')->nullable();
            $table->string('company_phone')->nullable();
            $table->string('company_file_npwp')->nullable();
            $table->string('company_file_skt')->nullable();
            $table->string('personal_file_ktp')->nullable();
            $table->string('marketing_name')->nullable();
            $table->json('transaction_history')->nullable();
            $table->string('verification_code')->nullable();
            $table->timestamp('verification_code_expires_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('password_reset_code')->nullable();
            $table->timestamp('password_reset_code_expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('customers'); }
};