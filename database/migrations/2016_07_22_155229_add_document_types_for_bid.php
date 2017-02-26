<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddDocumentTypesForBid extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $documentTypesForBids = [
            ['bid', 'technicalSpecifications', 'Технічні специфікації'],
            ['bid', 'qualificationDocuments', 'Документи, що підтверджують кваліфікацію'],
            ['bid', 'commercialProposal', 'Цінова пропозиція'],
            ['bid', 'billOfQuantity', 'Кошторис'],
            ['bid', 'eligibilityDocuments', 'Документи, що підтверджують відповідність'],
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
            'technicalSpecifications',
            'qualificationDocuments',
            'commercialProposal',
            'billOfQuantity',
            'eligibilityDocuments',
        ];
        foreach ($documentTypesForBids as $documentType) {
            DB::table('document_types')->where('document_type', $documentType)->delete();
        }
    }
}
