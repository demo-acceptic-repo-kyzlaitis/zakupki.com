<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddQualificationFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bids', function (Blueprint $table) {
            $table->integer('qualify_qualified')->nullable()->after('status');
            $table->integer('qualify_eligible')->nullable()->after('qualify_qualified');
            $table->string('qualify_unsuccessful_title')->nullable()->after('qualify_eligible');
            $table->string('qualify_unsuccessful_description')->nullable()->after('qualify_unsuccessful_title');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bids', function (Blueprint $table) {
            $table->dropColumn('qualify_qualified');
            $table->dropColumn('qualify_eligible');
            $table->dropColumn('qualify_unsuccessful_title');
            $table->dropColumn('qualify_unsuccessful_description');
        });
    }
}
