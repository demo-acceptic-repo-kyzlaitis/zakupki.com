<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class RemoveQualifyFromBids extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bids', function (Blueprint $table) {
            $table->dropColumn('qualify_qualified');
            $table->dropColumn('qualify_eligible');
            $table->dropColumn('qualify_unsuccessful_title');
            $table->dropColumn('qualify_unsuccessful_description');
        });

        $documentTypesForBids = [
            'protocol',
            'digital_signature',
        ];
        foreach ($documentTypesForBids as $documentType) {
            DB::table('document_types')->where('document_type', $documentType)->delete();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bids', function (Blueprint $table) {
            $table->integer('qualify_qualified')->nullable()->after('status');
            $table->integer('qualify_eligible')->nullable()->after('qualify_qualified');
            $table->string('qualify_unsuccessful_title')->nullable()->after('qualify_eligible');
            $table->string('qualify_unsuccessful_description')->nullable()->after('qualify_unsuccessful_title');
        });

        $documentTypesForBids = [
            ['bid', 'protocol', 'Протокол розляду'],
            ['bid', 'digital_signature', 'ЕЦП'],
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
}
