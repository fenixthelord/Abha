<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE form_fields MODIFY COLUMN type ENUM('text', 'number', 'date', 'dropdown', 'radio', 'checkbox', 'file', 'map') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE form_fields MODIFY COLUMN type ENUM('text', 'number', 'date', 'dropdown', 'radio', 'checkbox', 'file') NOT NULL");
    }
};
