<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeDateFieldsTenders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tenders', function ($table) {
            $table->datetime('enquiry_start_date')->nullable()->change();
            $table->datetime('enquiry_end_date')->nullable()->change();
            $table->datetime('tender_start_date')->nullable()->change();
            $table->datetime('tender_end_date')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
