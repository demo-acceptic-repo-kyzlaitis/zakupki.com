<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddConfidentialForBidDocuments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bid_documents', function (Blueprint $table) {
            $table->integer('type_id')->after('bid_id');
            $table->integer('confidential')->after('format');
            $table->string('confidential_cause')->nullable()->after('confidential');
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
            $table->dropColumn('type_id');
            $table->dropColumn('confidential');
            $table->dropColumn('confidentialCause');
        });
    }
}
