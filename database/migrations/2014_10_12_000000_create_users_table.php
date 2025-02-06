<?php

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
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('first_name');
            $table->string('last_name');
            $table->char('phone')->unique();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('image')->nullable();
            $table->string('alt')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->string('job')->nullable();
            $table->string('job_id')->nullable();
            $table->enum('role', ['super_admin', 'employee'])->default('employee');
            $table->boolean('is_admin')->default(false);
            $table->boolean('active')->default(true);
            $table->string('otp_code')->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->boolean('otp_verified')->default(false);
            $table->string('verify_code')->nullable();
            $table->string('refresh_token')->nullable();
            $table->timestamp('refresh_token_expires_at')->nullable();
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
