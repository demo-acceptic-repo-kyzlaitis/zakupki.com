<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddFieldsForQulificationInAward extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('awards', function (Blueprint $table) {
            $table->integer('eligible')->after('qualified');
            $table->string('unsuccessful_title')->nullable()->after('eligible');
            $table->string('unsuccessful_description')->nullable()->after('unsuccessful_title');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('awards', function (Blueprint $table) {
            $table->dropColumn('eligible');
            $table->dropColumn('unsuccessful_title');
            $table->dropColumn('unsuccessful_description');
        });
    }
}
