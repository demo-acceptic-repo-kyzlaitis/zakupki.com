<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddDateFieldsForDocuments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bid_documents', function (Blueprint $table) {
            $table->dateTime('date_published')->after('url');
            $table->dateTime('date_modified')->after('url');
        });
        Schema::table('award_documents', function (Blueprint $table) {
            $table->dateTime('date_published')->after('url');
            $table->dateTime('date_modified')->after('url');
        });
        Schema::table('cancellation_documents', function (Blueprint $table) {
            $table->dateTime('date_published')->after('url');
            $table->dateTime('date_modified')->after('url');
        });
        Schema::table('contract_documents', function (Blueprint $table) {
            $table->dateTime('date_published')->after('url');
            $table->dateTime('date_modified')->after('url');
        });
        Schema::table('plan_documents', function (Blueprint $table) {
            $table->dateTime('date_published')->after('url');
            $table->dateTime('date_modified')->after('url');
        });
        Schema::table('qualification_documents', function (Blueprint $table) {
            $table->dateTime('date_published')->after('url');
            $table->dateTime('date_modified')->after('url');
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
            $table->dropColumn('date_published');
            $table->dropColumn('date_modified');
        });
        Schema::table('award_documents', function (Blueprint $table) {
            $table->dropColumn('date_published');
            $table->dropColumn('date_modified');
        });
        Schema::table('cancellation_documents', function (Blueprint $table) {
            $table->dropColumn('date_published');
            $table->dropColumn('date_modified');
        });
        Schema::table('contract_documents', function (Blueprint $table) {
            $table->dropColumn('date_published');
            $table->dropColumn('date_modified');
        });
        Schema::table('plan_documents', function (Blueprint $table) {
            $table->dropColumn('date_published');
            $table->dropColumn('date_modified');
        });
        Schema::table('qualification_documents', function (Blueprint $table) {
            $table->dropColumn('date_published');
            $table->dropColumn('date_modified');
        });
    }
}
