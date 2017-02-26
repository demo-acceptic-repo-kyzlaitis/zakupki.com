<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProcurementMethodTypeToTenders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tenders', function(Blueprint $table)
        {
            //$table->integer('type_id')->after('tenderID');
        });
        Schema::table('procedure_types', function(Blueprint $table)
        {
            $table->string('procurement_method', 255)->after('id');
            //$table->string('procurement_method_type', 255)->after('id');
        });
        DB::table('procedure_types')
            ->where('id', 4)
            ->update(array('procurement_method' => 'limited', 'procurement_method_type' => 'reporting'));
        DB::table('statuses')
            ->insert(array('namespace' => 'tender', 'status' => 'active', 'description' => 'Активний', 'style' => 'info'));

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tenders', function($table)
        {
            $table->dropColumn('type_id');
        });
        Schema::table('procedure_types', function($table)
        {
            $table->dropColumn('procurement_method_type');
            $table->dropColumn('procurement_method');
        });
    }
}
