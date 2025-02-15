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
        Schema::table('positions', function (Blueprint $table) {

            DB::unprepared('
                CREATE TRIGGER prevent_self_parent_create BEFORE INSERT ON positions
                FOR EACH ROW
                BEGIN
                    IF NEW.parent_id = NEW.id THEN
                        SIGNAL SQLSTATE "45000" 
                        SET MESSAGE_TEXT = "A Position cannot be its own parent";
                    END IF;
                END;
            ');

            DB::unprepared('
                CREATE TRIGGER prevent_self_parent_edit BEFORE UPDATE ON positions
                FOR EACH ROW
                BEGIN
                    IF NEW.parent_id = NEW.id THEN
                        SIGNAL SQLSTATE "45000" 
                        SET MESSAGE_TEXT = "A Position cannot be its own parent";
                    END IF;
                END;
            ');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            DB::unprepared('DROP TRIGGER IF EXISTS prevent_self_parent_insert');
            DB::unprepared('DROP TRIGGER IF EXISTS prevent_self_parent_update');
        });
    }
};
