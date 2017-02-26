<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddFieldsForBidInAboveTresholdEu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bids', function (Blueprint $table) {
            $table->integer('self_qualified')->after('currency_id');
            $table->integer('self_eligible')->after('self_qualified');
            $table->text('subcontracting_details')->after('self_eligible');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bids', function (Blueprint $table) {
            $table->dropColumn('confidential');
            $table->dropColumn('confidentialCause');
            $table->dropColumn('subcontracting_details');
        });
    }
}
