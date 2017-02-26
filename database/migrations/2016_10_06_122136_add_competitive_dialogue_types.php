<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddCompetitiveDialogueTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('procedure_types')->insert([
            'procurement_method_type' => 'competitiveDialogueUA',
            'procurement_method' => 'open',
            'threshold_type' => 'above',
            'procedure_type' => 'cd',
            'procedure_name' => 'Конкурентний діалог',
            'active' => '1'
        ]);
        DB::table('procedure_types')->insert([
            'procurement_method_type' => 'competitiveDialogueEU',
            'procurement_method' => 'open',
            'threshold_type' => 'above',
            'procedure_type' => 'cde',
            'procedure_name' => 'Конкурентний діалог з публікацією англійською мовою',
            'active' => '1'
        ]);
        DB::table('procedure_types')->insert([
            'procurement_method_type' => 'competitiveDialogueUA.stage2',
            'procurement_method' => 'selective',
            'threshold_type' => 'above',
            'procedure_type' => 'cds2',
            'procedure_name' => '2-ий етап конкурентного діалогу',
            'active' => '0'
        ]);
        DB::table('procedure_types')->insert([
            'procurement_method_type' => 'competitiveDialogueEU.stage2',
            'procurement_method' => 'selective',
            'threshold_type' => 'above',
            'procedure_type' => 'cdes2',
            'procedure_name' => '2-ий етап конкурентного діалогу з публікацією англійською мовою',
            'active' => '0'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('procedure_types')->where('procurement_method_type', 'competitiveDialogueUA')->delete();
        DB::table('procedure_types')->where('procurement_method_type', 'competitiveDialogueEU')->delete();
        DB::table('procedure_types')->where('procurement_method_type', 'competitiveDialogueUA.stage2')->delete();
        DB::table('procedure_types')->where('procurement_method_type', 'competitiveDialogueEU.stage2')->delete();
    }
}
