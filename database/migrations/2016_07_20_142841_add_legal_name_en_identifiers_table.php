<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddLegalNameEnIdentifiersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('identifiers', function (Blueprint $table) {
            $table->longText('legal_name_en')->after('legal_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('identifiers', function (Blueprint $table) {
            $table->dropColumn('legal_name_en');
        });
    }
}
