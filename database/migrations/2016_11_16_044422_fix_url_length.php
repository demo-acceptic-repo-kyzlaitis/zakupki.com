<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class FixUrlLength extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('url', 512)->change();
        });
        Schema::table('bid_documents', function (Blueprint $table) {
            $table->string('url', 512)->change();
        });
        Schema::table('award_documents', function (Blueprint $table) {
            $table->string('url', 512)->change();
        });
        Schema::table('cancellation_documents', function (Blueprint $table) {
            $table->string('url', 512)->change();
        });
        Schema::table('complaint_documents', function (Blueprint $table) {
            $table->string('url', 512)->change();
        });
        Schema::table('contract_documents', function (Blueprint $table) {
            $table->string('url', 512)->change();
        });
        Schema::table('plan_documents', function (Blueprint $table) {
            $table->string('url', 512)->change();
        });
        Schema::table('qualification_documents', function (Blueprint $table) {
            $table->string('url', 512)->change();
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
