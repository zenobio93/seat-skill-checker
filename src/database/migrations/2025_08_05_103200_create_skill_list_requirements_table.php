<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('skill_list_requirements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('skill_list_id');
            $table->unsignedInteger('skill_id');
            $table->tinyInteger('required_level')->unsigned();
            $table->timestamps();

            $table->foreign('skill_list_id')->references('id')->on('skill_lists')->onDelete('cascade');
            $table->index(['skill_list_id', 'skill_id']);
            $table->unique(['skill_list_id', 'skill_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('skill_list_requirements');
    }
};