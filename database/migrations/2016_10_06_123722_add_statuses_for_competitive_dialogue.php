<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddStatusesForCompetitiveDialogue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $bidsStatuses = [
            ['active.stage2.pending', 'Очікування другого етапу', 'warning'],
            ['active.stage2.waiting', 'Перехід на другий етап', 'warning'],
            ['draft.stage2', 'Черновик другого етапу', 'default'],
        ];
        foreach ($bidsStatuses as $status) {
            DB::table('statuses')->insert([
                'namespace' => 'tender',
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
        DB::table('statuses')->where('namespace', 'tender')->where('status', 'active.stage2.pending')->delete();
        DB::table('statuses')->where('namespace', 'tender')->where('status', 'active.stage2.waiting')->delete();
        DB::table('statuses')->where('namespace', 'tender')->where('status', 'draft.stage2')->delete();
    }
}
