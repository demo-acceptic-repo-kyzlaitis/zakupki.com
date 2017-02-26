<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class RenameDocTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('document_types')->where('document_type', 'biddingDocuments')->update(['lang_ua' => 'Тендерна документація']);
        DB::table('document_types')->where('document_type', 'technicalSpecifications')->update(['lang_ua' => 'Технічний опис предмету закупівлі']);
        DB::table('document_types')->where('document_type', 'eligibilityCriteria')->update(['lang_ua' => 'Кваліфікаційні критерії']);
        DB::table('document_types')->where('document_type', 'evaluationCriteria')->update(['lang_ua' => 'Критерії оцінки']);
        DB::table('document_types')->insert(['document_type' => 'contractProforma', 'lang_ua' => 'Проект договору', 'namespace' => 'tender']);

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
