<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRationalTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rationale_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('title');
            $table->text('description');
        });

        $_data = json_decode(file_get_contents('http://standards.openprocurement.org/codelists/contract-change-rationale_type/uk.json'), true);
        foreach ($_data as $name => $description) {
            DB::table('rationale_types')->insert([
                'name' => $name,
                'title' => $description['title'],
                'description' => $description['description']
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
        Schema::drop('rationale_types');
    }
}
