<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddDocumentTypesForQualification extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $documentTypesForBids = [
            ['qualification', 'protocol', 'Протокол розляду'],
            ['qualification', 'digital_signature', 'ЕЦП'],
        ];
        foreach ($documentTypesForBids as $documentType) {
            DB::table('document_types')->insert([
                'namespace' => $documentType[0],
                'document_type' => $documentType[1],
                'lang_ua' => $documentType[2],
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $documentTypesForBids = [
            'protocol',
            'digital_signature',
        ];
        foreach ($documentTypesForBids as $documentType) {
            DB::table('document_types')->where('document_type', $documentType)->delete();
        }
    }
}
