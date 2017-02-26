<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddRecordToProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        /**
         * тут deleted подразумевает что предложение было удалено до раскрытия.
         * invalid это статус из таблицы statuses
         */
        DB::table('products')->insert([
            'name' => 'Возврат средств за переход bid в статус invalid или deleted',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        DB::table('products')->where('name', 'LIKE', 'Возврат средств за переход bid в статус invalid или deleted')->delete();
    }
}
