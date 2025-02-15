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
        Schema::dropIfExists('notification_details');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('notify_group_user');
        Schema::dropIfExists('notify_groups');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('notify_groups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string("name", 500); // Group/channel name
            $table->string("description", 500)->nullable();
            $table->string('model')->default('Users');
            $table->timestamps();
        });
        Schema::create('notify_group_user', function (Blueprint $table) {
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('notify_group_id')->constrained('notify_groups')->onDelete('cascade');
            $table->primary(['notify_group_id', 'user_id']);
        });
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sender_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->string('image')->nullable();
            $table->string('url')->nullable();
            $table->boolean('for_all')->default(false);
            $table->timestamp('schedule_at')->nullable();
            $table->timestamps();
        });
        Schema::create('notification_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('notification_id')->constrained('notifications')->onDelete('cascade');
            $table->enum('recipient_type', ['user', 'group']);
            $table->string('recipient_id');
            $table->timestamps();
        });
    }
};
