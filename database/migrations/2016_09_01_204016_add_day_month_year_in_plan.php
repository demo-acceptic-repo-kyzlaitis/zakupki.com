<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddDayMonthYearInPlan extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->string('start_day', 2)->nullable()->after('code_kekv_id');
            $table->string('start_month', 2)->after('start_day');
            $table->string('start_year', 4)->after('start_month');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('start_day');
            $table->dropColumn('start_month');
            $table->dropColumn('start_year');
        });
    }
}
