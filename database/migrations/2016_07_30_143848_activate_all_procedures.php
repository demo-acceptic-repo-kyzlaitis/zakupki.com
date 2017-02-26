<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class ActivateAllProcedures extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('procedure_types')->where('id', 5)->update(['active' => '1']);
        DB::table('procedure_types')->where('id', 6)->update(['active' => '1', 'procedure_name' => 'Переговорна процедура скорочена']);
        DB::table('procedure_types')->where('id', 8)->update(['active' => '1']);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('procedure_types')->where('id', 5)->update(['active' => '0']);
        DB::table('procedure_types')->where('id', 6)->update(['active' => '0']);
        DB::table('procedure_types')->where('id', 8)->update(['active' => '0']);
    }
}
