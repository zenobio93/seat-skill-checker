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
        Schema::table('skill_lists', function (Blueprint $table) {
            $table->unsignedInteger('priority')->default(0)->after('description');
            $table->boolean('is_required')->default(false)->after('priority');
            
            $table->index(['priority', 'is_required']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('skill_lists', function (Blueprint $table) {
            $table->dropIndex(['priority', 'is_required']);
            $table->dropColumn(['priority', 'is_required']);
        });
    }
};