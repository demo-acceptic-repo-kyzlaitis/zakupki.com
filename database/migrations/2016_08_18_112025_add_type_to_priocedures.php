<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddTypeToPriocedures extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('procedure_types', function (Blueprint $table) {
            $table->string('threshold_type')->after('procurement_method');
        });
        DB::table('procedure_types')->where('procurement_method_type', 'belowThreshold')->update(['threshold_type' => 'below']);
        DB::table('procedure_types')->where('procurement_method_type', 'aboveThresholdUA')->update(['threshold_type' => 'above']);
        DB::table('procedure_types')->where('procurement_method_type', 'aboveThresholdEU')->update(['threshold_type' => 'above']);
        DB::table('procedure_types')->where('procurement_method_type', 'reporting')->update(['threshold_type' => 'below.limited']);
        DB::table('procedure_types')->where('procurement_method_type', 'negotiation')->update(['threshold_type' => 'above.limited']);
        DB::table('procedure_types')->where('procurement_method_type', 'negotiation.quick')->update(['threshold_type' => 'above.limited']);
        DB::table('procedure_types')->where('procurement_method_type', 'aboveThresholdUA.defense')->update(['threshold_type' => 'above']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('procedure_type', function (Blueprint $table) {
            $table->dropColumn('threshold_type');
        });
    }
}
