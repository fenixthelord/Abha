<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflows', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('workflow_blocks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('workflow_id')->constrained('workflows')->onDelete('cascade');
            $table->enum('type', ['start', 'end', 'action', 'scheduled', 'system', 'conditional']);
            $table->integer('order');
            $table->json('config')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // Schema::create('workflow_actions', function (Blueprint $table) {
        //     $table->uuid('id')->primary();
        //     $table->foreignUuid('workflow_block_id')->constrained('workflow_blocks')->onDelete('cascade');
        //     $table->enum('action_type', ['data_creation', 'data_deletion', 'field_change'])->nullable();
        //     $table->uuidMorphs('actionable'); // event_id, category_id, table_id
        //     $table->timestamps();
        // });

        // Schema::create('workflow_schedules', function (Blueprint $table) {
        //     $table->uuid('id')->primary();
        //     $table->foreignUuid('workflow_block_id')->constrained('workflow_blocks')->onDelete('cascade');
        //     $table->enum('schedule_type', ['time_based', 'recurring']);
        //     $table->dateTime('time_based_date')->nullable();
        //     $table->date('start_date')->nullable();
        //     $table->time('time')->nullable();
        //     $table->enum('recurring_type', ['daily', 'weekly', 'monthly', 'annually'])->nullable();
        //     $table->json('recurring_details')->nullable(); // Stores weekday, day of month, or annual day/month
        //     $table->timestamps();
        // });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_blocks');
        Schema::dropIfExists('workflows');
    }
};
