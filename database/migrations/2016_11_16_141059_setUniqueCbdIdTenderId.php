<?php

use Illuminate\Database\Migrations\Migration;

class SetUniqueCbdIdTenderId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
//        DB::statement('ALTER TABLE contracts ADD CONSTRAINT uc_cbdAwardStatus UNIQUE (cbd_id,award_id,status)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
//        DB::statement('ALTER TABLE contracts DROP INDEX uc_cbdAwardStatus');
    }
}
