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
        // Rename skill_plans table to skill_plans
        Schema::rename('skill_lists', 'skill_plans');

        // Rename skill_plan_requirements table to skill_plan_requirements
        Schema::rename('skill_list_requirements', 'skill_plan_requirements');

        // Update foreign key column name in skill_plan_requirements table
        Schema::table('skill_plan_requirements', function (Blueprint $table) {
            $table->renameColumn('skill_list_id', 'skill_plan_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert foreign key column name in skill_plan_requirements table
        Schema::table('skill_plan_requirements', function (Blueprint $table) {
            $table->renameColumn('skill_plan_id', 'skill_list_id');
        });

        // Revert skill_plan_requirements table to skill_plan_requirements
        Schema::rename('skill_plan_requirements', 'skill_list_requirements');

        // Revert skill_plans table to skill_plans
        Schema::rename('skill_plans', 'skill_lists');
    }
};