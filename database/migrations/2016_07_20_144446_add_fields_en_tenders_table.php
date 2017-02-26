<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddFieldsEnTendersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tenders', function (Blueprint $table) {
            $table->text('title_en')->after('title')->nullable();
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
        Schema::table('tenders', function (Blueprint $table) {
            $table->dropColumn('title_en');
            $table->dropColumn('contact_name_en');
            $table->dropColumn('contact_available_lang');
        });
    }
}
