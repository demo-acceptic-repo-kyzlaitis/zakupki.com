<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProcTypeIdToTenders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tenders', function(Blueprint $table)
        {
            //$table->integer('procurement_type_id')->after('tenderID');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tenders', function($table)
        {
            $table->dropColumn('procurement_type_id');
        });
    }
}
