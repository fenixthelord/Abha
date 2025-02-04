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
        Schema::table('categories', function (Blueprint $table) {
            $table->unique([
                "name",
                "parent_id",
            ]);
            // Create trigger to prevent self-referencing
            // DB::unprepared('
            //     CREATE TRIGGER prevent_self_parent_insert BEFORE INSERT ON categories
            //     FOR EACH ROW
            //     BEGIN
            //         IF NEW.parent_id = NEW.id THEN
            //             SIGNAL SQLSTATE "45000" 
            //             SET MESSAGE_TEXT = "A category cannot be its own parent";
            //         END IF;
            //     END;
            // ');

            // DB::unprepared('
            //     CREATE TRIGGER prevent_self_parent_update BEFORE UPDATE ON categories
            //     FOR EACH ROW
            //     BEGIN
            //         IF NEW.parent_id = NEW.id THEN
            //             SIGNAL SQLSTATE "45000" 
            //             SET MESSAGE_TEXT = "A category cannot be its own parent";
            //         END IF;
            //     END;
            // ');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique([
                "name",
                "parent_id",
            ]);
            // DB::unprepared('DROP TRIGGER IF EXISTS prevent_self_parent_insert');
            // DB::unprepared('DROP TRIGGER IF EXISTS prevent_self_parent_update');
        });
    }
};
