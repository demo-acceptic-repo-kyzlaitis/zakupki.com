<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddComplaintPeriodToTender extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tenders', function (Blueprint $table) {
            $table->dateTime('complaint_date_start')->after('tender_end_date')->nullable();
            $table->dateTime('complaint_date_end')->after('tender_end_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tenders', function (Blueprint $table) {
            $table->dropColumn('complaint_date_start');
            $table->dropColumn('complaint_date_end');
        });
    }
}
