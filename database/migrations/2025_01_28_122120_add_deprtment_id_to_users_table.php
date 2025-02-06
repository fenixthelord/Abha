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
        Schema::table('users', function (Blueprint $table) {
            // $table->unsignedBigInteger("department_id")->after('id')->nullable();
            $table->foreignUuid('department_id')->after('id')->nullable()->constrained('departments')->nullOnDelete()->onUpdate("cascade");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign("users_department_id_foreign");
            $table->dropColumn("department_id");
        });
    }
};
