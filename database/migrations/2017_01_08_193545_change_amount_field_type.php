<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeAmountFieldType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bids', function (Blueprint $table) {
            $table->bigInteger('payment_amount')->change();
        });
        Schema::table('contracts', function (Blueprint $table) {
            $table->bigInteger('amount_paid')->change();
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->bigInteger('amount')->change();
        });
        Schema::table('transactions', function (Blueprint $table) {
            $table->bigInteger('amount')->change();
            $table->bigInteger('balance')->change();
        });
        Schema::table('user_balance', function (Blueprint $table) {
            $table->bigInteger('amount')->change();
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
