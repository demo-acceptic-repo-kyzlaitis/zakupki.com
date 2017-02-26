<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUploadUrlToDocs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('upload_url', 1024)->after('url');
            $table->string('hash', 255)->after('url');
        });
        Schema::table('award_documents', function (Blueprint $table) {
            $table->string('upload_url', 1024)->after('url');
            $table->string('hash', 255)->after('url');
        });
        Schema::table('bid_documents', function (Blueprint $table) {
            $table->string('upload_url', 1024)->after('url');
            $table->string('hash', 255)->after('url');
        });
        Schema::table('cancellation_documents', function (Blueprint $table) {
            $table->string('upload_url', 1024)->after('url');
            $table->string('hash', 255)->after('url');
        });
        Schema::table('complaint_documents', function (Blueprint $table) {
            $table->string('upload_url', 1024)->after('url');
            $table->string('hash', 255)->after('url');
        });
        Schema::table('contract_documents', function (Blueprint $table) {
            $table->string('upload_url', 1024)->after('url');
            $table->string('hash', 255)->after('url');
        });
        Schema::table('plan_documents', function (Blueprint $table) {
            $table->string('upload_url', 1024)->after('url');
            $table->string('hash', 255)->after('url');
        });
        Schema::table('qualification_documents', function (Blueprint $table) {
            $table->string('upload_url', 1024)->after('url');
            $table->string('hash', 255)->after('url');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('upload_url');
            $table->dropColumn('hash');
        });
        Schema::table('award_documents', function (Blueprint $table) {
            $table->dropColumn('upload_url');
            $table->dropColumn('hash');
        });
        Schema::table('bid_documents', function (Blueprint $table) {
            $table->dropColumn('upload_url');
            $table->dropColumn('hash');
        });
        Schema::table('cancellation_documents', function (Blueprint $table) {
            $table->dropColumn('upload_url');
            $table->dropColumn('hash');
        });
        Schema::table('complaint_documents', function (Blueprint $table) {
            $table->dropColumn('upload_url');
            $table->dropColumn('hash');
        });
        Schema::table('contract_documents', function (Blueprint $table) {
            $table->dropColumn('upload_url');
            $table->dropColumn('hash');
        });
        Schema::table('plan_documents', function (Blueprint $table) {
            $table->dropColumn('upload_url');
            $table->dropColumn('hash');
        });
        Schema::table('qualification_documents', function (Blueprint $table) {
            $table->dropColumn('upload_url');
            $table->dropColumn('hash');
        });
    }
}
