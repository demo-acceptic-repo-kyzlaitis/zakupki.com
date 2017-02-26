<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddDescDecisionForBidDocuments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bid_documents', function (Blueprint $table) {
            $table->integer('description_decision')->after('confidential_cause');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bid_documents', function (Blueprint $table) {
            $table->dropColumn('description_decision');
        });
    }
}
