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
        Schema::create('form_submission_values', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('form_submission_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('form_field_id')->constrained()->onDelete('cascade');
            $table->text('value'); // Store user input            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_submission_values');
    }
};
