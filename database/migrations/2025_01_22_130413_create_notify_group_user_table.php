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
            $table->uuid('notify_group_uuid'); // Use UUID for notify group
            $table->uuid('user_uuid'); // Use UUID for user
            $table->primary(['notify_group_uuid', 'user_uuid']); // Composite primary key

            // Foreign key constraints
            $table->foreign('notify_group_uuid')
                ->references('uuid')
                ->on('notify_groups')
                ->onDelete('cascade');
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
