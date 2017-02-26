<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddBidDocTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('document_types')->insert([
            'namespace' => 'bid',
            'document_type' => 'eligibilityDocuments',
            'lang_ua' => 'Документи, що підтверджують відповідність',
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
        DB::table('document_types')->insert([
            'namespace' => 'bid',
            'document_type' => 'billOfQuantity',
            'lang_ua' => 'Кошторис',
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('statuses')->where('namespace', 'bid')->where('document_type', 'eligibilityDocuments')->delete();
        DB::table('statuses')->where('namespace', 'bid')->where('document_type', 'billOfQuantity')->delete();
    }
}
