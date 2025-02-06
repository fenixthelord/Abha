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
        Schema::create('events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger("service_id");
            $table->foreign("service_id")->references("id")->on("services")->onDelete("cascade");
            $table->string('name' , 500);
            $table->string("details");
            $table->string("image");
            $table->dateTime("start_date");
            $table->date("end_date");
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
