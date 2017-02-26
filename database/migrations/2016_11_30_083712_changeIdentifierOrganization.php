<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeIdentifierOrganization extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('identifier_organisation', function (Blueprint $table) {
            $table->string('identifier')->after('identifier_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('identifier_organisation', function (Blueprint $table) {
            $table->dropColumn('identifier');
        });
    }
}
