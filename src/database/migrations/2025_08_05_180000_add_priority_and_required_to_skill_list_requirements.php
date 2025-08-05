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
        Schema::table('skill_list_requirements', function (Blueprint $table) {
            $table->unsignedInteger('priority')->default(0)->after('required_level');
            $table->boolean('is_required')->default(true)->after('priority');
            
            $table->index(['skill_list_id', 'priority']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('skill_list_requirements', function (Blueprint $table) {
            $table->dropIndex(['skill_list_id', 'priority']);
            $table->dropColumn(['priority', 'is_required']);
        });
    }
};