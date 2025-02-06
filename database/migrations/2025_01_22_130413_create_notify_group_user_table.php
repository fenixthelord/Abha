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
            // $table->uuid('notify_group_id'); // Use UUID for notify group
            // $table->uuid('user_id'); // Use UUID for user
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('notify_group_id')->constrained('notify_groups')->onDelete('cascade');
            $table->primary(['notify_group_id', 'user_id']); // Composite primary key
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
