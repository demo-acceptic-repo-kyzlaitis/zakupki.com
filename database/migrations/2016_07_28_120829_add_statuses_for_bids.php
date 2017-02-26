<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddStatusesForBids extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $bidsStatuses = [
            ['draft', 'Не опублікована', 'default'],
            ['pending', 'В очікуванні', 'warning'],
            ['active', 'Активна', 'warning'],
            ['inactive', 'Неактивна', 'danger'],
            ['unsuccessful', 'Відхилено', 'danger'],
        ];
        foreach ($bidsStatuses as $status) {
            DB::table('statuses')->insert([
                'namespace' => 'bid',
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
            'draft',
            'pending',
            'active',
            'inactive',
            'unsuccessful',
        ];
        foreach ($bidsStatuses as $status) {
            DB::table('statuses')
                ->where('namespace', 'bid')
                ->where('status', $status)
                ->delete();
        }
    }
}
