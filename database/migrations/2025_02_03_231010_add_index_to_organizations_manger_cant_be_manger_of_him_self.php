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
    public function up()
    {
        // Add a CHECK constraint to enforce employee_id != manger_id
        // DB::statement('
        //     ALTER TABLE organizations 
        //     ADD CONSTRAINT check_employee_manger_not_equal 
        //     CHECK (employee_id != manger_id)
        // ');
    }

    public function down()
    {
        // Remove the CHECK constraint in the down() method
        DB::statement('
            ALTER TABLE organizations 
            DROP CHECK check_employee_manger_not_equal
        ');
    }
};
