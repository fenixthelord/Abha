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
            $table->foreignUuid("position_id")->after("id")->nullable()->constrained("positions")->onDelete('cascade');
            $table->foreignUuid("form_id")->after("id")->nullable()->constrained("forms")->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(["position_id"]);
            $table->dropColumn("position_id");

            $table->dropForeign(["form_id"]);
            $table->dropColumn("form_id");
        });
    }
};
