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
        Schema::create('form_field_data_sources', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('form_field_id')->constrained('form_fields')->onDelete('cascade');
            $table->string('source_table');
            $table->string('source_column');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_field_data_sources');
    }
};
