<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusToProcedures extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('procedure_types', function(Blueprint $table)
        {
            $table->integer('active');
        });
        DB::table('procedure_types')
            ->where('id', 1)
            ->update(array('active' => '1'));
        DB::table('procedure_types')
            ->where('id', 4)
            ->update(array('active' => '1'));
        DB::table('procedure_types')
            ->where('id', 1)
            ->update(array('procurement_method' => 'open', 'procurement_method_type' => 'belowThreshold'));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('procedure_types', function(Blueprint $table)
        {
            $table->dropColumn('active');
        });
    }
}
