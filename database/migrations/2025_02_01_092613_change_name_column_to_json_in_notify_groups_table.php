<?php

use Illuminate\Broadcasting\Channel;
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
        Schema::table('notify_groups', function (Blueprint $table) {
            $table->json("name")->change();
            $table->json("description")->change();
        }); 
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notify_groups', function (Blueprint $table) {
            $table->string("name")->change();
        });
    }
};
