<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddContentForGroundsForRejection extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $bidsStatuses = [
            ['pre-qualification', 'participant', 'qualification', 'Не відповідає кваліфікаційним критеріям', 'Учасник не відповідає кваліфікаційним (кваліфікаційному) критеріям, установленим в тендерній документації', 1],
            ['pre-qualification', 'participant', 'eligible', 'Наявні підстави, зазначені у статті 17', 'Наявні підстави для відхилення тендерної пропозиції, зазначені у статті 17 і частині сьомій статті 28 Закону Про публічні закупівлі', 1],
            ['pre-qualification', 'participant', 'documents', 'Не відповідає вимогам тендерної документації', 'Тендерна пропозиція не відповідає вимогам тендерної документації', 1],
            ['pre-qualification', 'winner', 'qualification', 'Відмовився від підписання договору', 'Переможець відмовився від підписання договору про закупівлю відповідно до вимог тендерної документації або укладення договору про закупівлю', 1],
            ['pre-qualification', 'winner', 'eligible', 'Не надав документи по ст.17', 'Переможець не надав документи, що підтверджують відсутність підстав, передбачених ст 17 Закону, або надані документи не підтверджують відсутність підстав, передбачених ст.17 Закону', 1],
        ];
        foreach ($bidsStatuses as $status) {
            DB::table('grounds_for_rejection')->insert([
                'namespace' => $status[0],
                'bid_status' => $status[1],
                'code' => $status[2],
                'title' => $status[3],
                'description' => $status[4],
                'active' => $status[5],
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
            ['pre-qualification', 'participant', 'qualification'],
            ['pre-qualification', 'participant', 'eligible'],
            ['pre-qualification', 'participant', 'documents'],
            ['pre-qualification', 'winner', 'qualification'],
            ['pre-qualification', 'winner', 'eligible'],
        ];
        foreach ($bidsStatuses as $status) {
            DB::table('grounds_for_rejection')
                ->where('namespace', $status[0])
                ->where('bid_status', $status[1])
                ->where('code', $status[2])
                ->delete();
        }
    }
}
