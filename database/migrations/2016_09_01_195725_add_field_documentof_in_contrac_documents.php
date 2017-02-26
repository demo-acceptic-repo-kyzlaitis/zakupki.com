<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddFieldDocumentofInContracDocuments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contract_documents', function (Blueprint $table) {
            $table->text('document_of')->after('url');
            $table->integer('change_id')->after('document_of');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contract_documents', function (Blueprint $table) {
            $table->dropColumn('document_of');
            $table->dropColumn('change_id');
        });
    }
}
