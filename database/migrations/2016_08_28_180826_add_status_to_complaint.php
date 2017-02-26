<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddStatusToComplaint extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $bidsStatuses = [
            ['stopping', 'Відхилено скражником', 'danger']
        ];
        foreach ($bidsStatuses as $status) {
            DB::table('statuses')->insert([
                'namespace' => 'complaint',
                'status' => $status[0],
                'description' => $status[1],
                'style' => $status[2],
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
        //
    }
}
