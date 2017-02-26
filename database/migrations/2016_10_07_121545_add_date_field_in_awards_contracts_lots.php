<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddDateFieldInAwardsContractsLots extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('awards', function (Blueprint $table) {
            $table->dateTime('date')->after('currency_id');
        });
        Schema::table('contracts', function (Blueprint $table) {
            $table->dateTime('date')->after('amount_paid');
        });
        Schema::table('lots', function (Blueprint $table) {
            $table->dateTime('date')->after('status_id');
        });
        Schema::table('cancellations', function (Blueprint $table) {
            $table->dateTime('date')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('awards', function (Blueprint $table) {
            $table->dropColumn('date');
        });
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn('date');
        });
        Schema::table('lots', function (Blueprint $table) {
            $table->dropColumn('date');
        });
        Schema::table('cancellations', function (Blueprint $table) {
            $table->dropColumn('date');
        });
    }
}
