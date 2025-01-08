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
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone')->unique();
            $table->string('image')->nullable();
            $table->Enum('gender', ['male', 'female'])->default('male');
            $table->string('alt');
            $table->enum('rloe',['admin','user']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('photo');
            $table->dropColumn('phone');
            $table->dropColumn('last_name');
            $table->dropColumn('first_name');
            $table->dropColumn('gender');
            $table->dropColumn('alt');
            $table->dropColumn('role');


        });
    }
};
