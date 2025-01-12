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
            $table->dropColumn('name');
            $table->uuid('uuid');
            $table->string('first_name');
            $table->string('last_name');
            $table->char('phone')->unique();
            $table->string('image')->nullable();
            $table->string('alt')->nullable();
            $table->enum('gender',['male','female'])->nullable();
            $table->string('OTP')->nullable();
            $table->string('jop')->nullable();
            $table->string('jop_id')->nullable();
            $table->enum('role',['super_admin','employee'])->default('employee');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('uuid');
            $table->dropColumn('first_name');
            $table->dropColumn('last_name');
            $table->dropColumn('phone');
            $table->dropColumn('image');
            $table->dropColumn('alt');
            $table->dropColumn('gender');
            $table->dropColumn('OTP');
            $table->dropColumn('jop');
            $table->dropColumn('jop_id');
            $table->dropColumn('role');
            $table->string('name');
        });
    }
};
