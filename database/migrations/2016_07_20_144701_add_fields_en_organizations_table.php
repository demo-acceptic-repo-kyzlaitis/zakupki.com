<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddFieldsEnOrganizationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->text('name_en')->after('name')->nullable();
            $table->string('contact_name_en')->after('contact_name');
            $table->string('contact_available_lang', 2)->after('contact_email');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn('name_en');
            $table->dropColumn('contact_name_en');
            $table->dropColumn('contact_available_lang');
        });
    }
}
