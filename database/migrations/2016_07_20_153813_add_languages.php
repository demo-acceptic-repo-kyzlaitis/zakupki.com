<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddLanguages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('languages')->truncate();

        $languages = [
            'ua' => 'Українська',
            'ru' => 'Русский',
            'en' => 'English',
        ];
        foreach ($languages as $code => $name) {
            DB::table('languages')->insert([
                'language_code' => $code,
                'language_name' => $name,
                'active' => 1,
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
        DB::table('languages')->truncate();
    }
}
