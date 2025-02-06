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
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->uuid();

            $table->unsignedBigInteger("department_id");
            $table->foreign("department_id")->references("id")->on("departments")->onDelete("cascade");

            $table->unsignedBigInteger("manger_id")->nullable();
            $table->foreign("manger_id")->references("id")->on("users")->onDelete("cascade");

            $table->unsignedBigInteger("employee_id");
            $table->foreign("employee_id")->references("id")->on("users")->onDelete("cascade");

            $table->string("position", 500)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
