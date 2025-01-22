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
        Schema::create('notify_group_user', function (Blueprint $table) {
            $table->foreignId('notify_group_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id');
            $table->primary(['notify_group_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notify_group_user');
    }
};
