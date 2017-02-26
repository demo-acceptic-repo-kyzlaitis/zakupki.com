<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exports', function (Blueprint $table) {
            $table->increments('id');
            /*
             *  колонка id с таблицы тендера
             * */
            $table->integer('tender_id')->unsigned();
            /*
             * типы могут быть как protocol, result, appeal .....
             * */
            $table->string('feed_type');
            /*
             * Время экспорта для скрипта который будет выполнять запросы по уведомлению о новых данных
             * */
            $table->dateTime('sent_to_export')->nullable();
            
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
        Schema::drop('exports');
    }
}
