<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFormatToCancelleationDocuemnts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cancellation_documents', function(Blueprint $table)
        {
            $table->string('format', 255)->after('url');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cancellation_documents', function ($table) {
            $table->dropColumn('format');
        });
    }
}
