<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypeToContractDocuments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
//        Schema::table('contract_documents', function (Blueprint $table) {
//            $table->integer('type_id')->nullable(false);
//        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
//        Schema::table('contract_documents', function (Blueprint $table) {
//            $table->dropColumn('type_id');
//        });
    }
}
