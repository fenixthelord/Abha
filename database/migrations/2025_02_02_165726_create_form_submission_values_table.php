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
            $table->uuid('form_submission_id');
            $table->foreign('form_submission_id')->references('id')->on('form_submissions');
            $table->uuid('form_field_id');
            $table->foreign('form_field_id')->references('id')->on('form_fields');
            $table->text('value');
            $table->softDeletes();
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
