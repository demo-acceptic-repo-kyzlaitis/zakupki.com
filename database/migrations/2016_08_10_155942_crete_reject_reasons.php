<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreteRejectReasons extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reject_reasons', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('description');
            $table->string('status');
            $table->string('procurement_method_type');
        });

        DB::table('reject_reasons')->insert([
            'title' => 'Не відповідає кваліфікаційним критеріям',
            'description' => 'Учасник не відповідає кваліфікаційним (кваліфікаційному) критеріям, установленим в тендерній документації',
            'status' => 'pending',
            'procurement_method_type' => 'open'
        ]);
        DB::table('reject_reasons')->insert([
            'title' => 'Наявні підстави, зазначені у статті 17',
            'description' => 'Наявні підстави для відхилення тендерної пропозиції, зазначені у статті 17 і частині сьомій статті 28 Закону Про публічні закупівлі',
            'status' => 'pending',
            'procurement_method_type' => 'open'
        ]);
        DB::table('reject_reasons')->insert([
            'title' => 'Не відповідає вимогам тендерної документації',
            'description' => 'Тендерна пропозиція не відповідає вимогам тендерної документації',
            'status' => 'pending',
            'procurement_method_type' => 'open'
        ]);
        DB::table('reject_reasons')->insert([
            'title' => 'Відмовився від підписання договору',
            'description' => 'Переможець відмовився від підписання договору про закупівлю відповідно до вимог тендерної документації або укладення договору про закупівлю',
            'status' => 'active',
            'procurement_method_type' => 'open'
        ]);
        DB::table('reject_reasons')->insert([
            'title' => 'Не надав документи по ст.17',
            'description' => 'Переможець не надав документи, що підтверджують відсутність підстав, передбачених ст 17 Закону, або надані документи не підтверджують відсутність підстав, передбачених ст.17 Закону',
            'status' => 'active',
            'procurement_method_type' => 'open'
        ]);
        DB::table('reject_reasons')->insert([
            'title' => 'Не відповідає кваліфікаційним критеріям',
            'description' => 'Учасник не відповідає кваліфікаційним (кваліфікаційному) критеріям, установленим в тендерній документації',
            'status' => 'pending',
            'procurement_method_type' => 'defense'
        ]);
        DB::table('reject_reasons')->insert([
            'title' => 'Наявні підстави, зазначені у статті 17',
            'description' => 'Наявні підстави для відхилення тендерної пропозиції, зазначені у статті 17 і частині сьомій статті 28 Закону Про публічні закупівлі',
            'status' => 'pending',
            'procurement_method_type' => 'defense'
        ]);
        DB::table('reject_reasons')->insert([
            'title' => 'Не відповідає вимогам тендерної документації',
            'description' => 'Тендерна пропозиція не відповідає вимогам тендерної документації',
            'status' => 'pending',
            'procurement_method_type' => 'defense'
        ]);
        DB::table('reject_reasons')->insert([
            'title' => 'Ненадання або несвоєчасне надання учасником одиниці товару на перевірку відповідності зразку-еталону ',
            'description' => 'та/або іншим вимогам замовника до предмета закупівлі, зазначеним в оголошенні про проведення відбору, або документів про підтвердження відповідності товару, роботи чи послуги технічним, якісним та кількісним характеристикам предмета закупівлі, визначеним замовником',
            'status' => 'pending',
            'procurement_method_type' => 'defense'
        ]);
        DB::table('reject_reasons')->insert([
            'title' => 'Невідповідність товарів, робіт чи послуг учасника технічним, якісним та кількісним характеристикам предмета закупівлі',
            'description' => '',
            'status' => 'pending',
            'procurement_method_type' => 'defense'
        ]);
        DB::table('reject_reasons')->insert([
            'title' => 'Відсутність підтвердження подання забезпечення цінової пропозиції ',
            'description' => '',
            'status' => 'pending',
            'procurement_method_type' => 'defense'
        ]);
        DB::table('reject_reasons')->insert([
            'title' => 'Відмова або неучасть учасника відбору в переговорах, призначених замовником',
            'description' => 'у визначену замовником дату їх проведення',
            'status' => 'pending',
            'procurement_method_type' => 'defense'
        ]);
        DB::table('reject_reasons')->insert([
            'title' => 'Відмова учасника відбору від участі в переговорах з ціною/приведеною ціною, поданою ним за результатами проведеного аукціону',
            'description' => '',
            'status' => 'pending',
            'procurement_method_type' => 'defense'
        ]);
        DB::table('reject_reasons')->insert([
            'title' => 'Неусунення учасником недоліків у поданих ним документах протягом наступних 24 годин з моменту ознайомлення учасника з такими недоліками під час проведення переговорів',
            'description' => '',
            'status' => 'pending',
            'procurement_method_type' => 'defense'
        ]);
        DB::table('reject_reasons')->insert([
            'title' => 'Учасник протягом одного року до дати проведення електронного аукціону вчинив порушення в одного й того самого замовника',
            'description' => 'не приймав участі або відмовився від участі в переговорній процедурі закупівлі до оприлюднення повідомлення про намір укласти договір - більше трьох разів;
відмовився від підписання договору про закупівлю у визначені замовником терміни - більше двох разів;
не виконав умови (умов) договору щодо якості та/або терміну поставлених товарів, виконаних робіт, наданих послуг - більше одного разу',
            'status' => 'pending',
            'procurement_method_type' => 'defense'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('reject_reasons');
    }
}
