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
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('OTP', 'otp_code');
            $table->dropColumn('verify_code');
            $table->timestamp('otp_expires_at')->nullable();
            $table->boolean('otp_verified')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('otp_expires_at');
            $table->dropColumn('otp_verified');
            $table->string('verify_code')->nullable();
            $table->renameColumn('OTP', 'otp_code');


        });
    }
};
