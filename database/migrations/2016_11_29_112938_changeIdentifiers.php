<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeIdentifiers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('identifiers', function (Blueprint $table) {
            $table->renameColumn('legal_name', 'name');
            $table->renameColumn('legal_name_en', 'description');
            $table->string('country_iso')->after('id');
            $table->string('scheme')->change();
            $table->boolean('status')->default('0')->after('uri');
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
            $table->renameColumn('name', 'legal_name');
            $table->renameColumn('description', 'legal_name_en');
            $table->dropColumn('country_iso');
            $table->dropColumn('status');
        });
    }
}
