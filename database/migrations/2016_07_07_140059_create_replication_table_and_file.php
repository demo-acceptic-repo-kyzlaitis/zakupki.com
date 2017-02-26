<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReplicationTableAndFile extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $fp = fopen('storage/replica.sqlite', "w");
        fclose($fp);
                Schema::connection('replica')->create('replications', function($table)
        {
            $table->increments('id');
            $table->integer('entity_id');
            $table->string('entity_type',999);
            $table->string('access_token');
            $table->timestamps();
        });
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
