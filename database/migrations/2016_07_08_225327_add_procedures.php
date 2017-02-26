<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProcedures extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('procedure_types')->where('id', 1)->update(['procurement_method' => 'open', 'procurement_method_type' => 'belowThreshold']);
        DB::table('procedure_types')->where('id', 2)->update(['procurement_method' => 'open', 'procurement_method_type' => 'aboveThresholdUA']);
        DB::table('procedure_types')->where('id', 3)->update(['procurement_method' => 'open', 'procurement_method_type' => 'aboveThresholdEU']);
        DB::table('procedure_types')->where('id', 4)->update(['procurement_method' => 'limited', 'procurement_method_type' => 'reporting']);
        DB::table('procedure_types')->where('id', 5)->update(['procurement_method' => 'limited', 'procurement_method_type' => 'negotiation']);
        DB::table('procedure_types')->where('id', 6)->update(['procurement_method' => 'limited', 'procurement_method_type' => 'negotiation.quick']);
        DB::table('procedure_types')->where('id', 7)->update(['procurement_method' => '', 'procurement_method_type' => '']);
        DB::table('procedure_types')->where('id', 8)->update(['procurement_method' => 'open', 'procurement_method_type' => 'aboveThresholdUA.defense']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
