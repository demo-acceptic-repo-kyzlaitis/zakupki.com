<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddStatusesForQualifications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $bidsStatuses = [
            ['pending', 'В очікуванні', 'warning'],
            ['active', 'Активна', 'success'],
            ['unsuccessful', 'Відхилено', 'danger'],
        ];
        foreach ($bidsStatuses as $status) {
            DB::table('statuses')->insert([
                'namespace' => 'qualification',
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
        $bidsStatuses = [
            'pending',
            'active',
            'unsuccessful',
        ];
        foreach ($bidsStatuses as $status) {
            DB::table('statuses')
                ->where('namespace', 'qualification')
                ->where('status', $status)
                ->delete();
        }
    }
}
