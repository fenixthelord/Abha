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
        Schema::create('notify_groups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string("name", 500); // Group/channel name
            $table->string("description", 500)->nullable();
            $table->string('model')->default('Users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notify_groups');
    }
};
