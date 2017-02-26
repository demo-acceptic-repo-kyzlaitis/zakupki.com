<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call('RegionsTableSeeder');
        $this->call('UnitsTableSeeder');
        $this->call('StatusesTableSeeder');
        $this->call('DocumentTypeTableSeeder');
        $this->call('CurrenciesTypeTableSeeder');
        $this->call('NotificationTemplatesSeeder');

        Model::reguard();
    }


}


class RegionsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('tenders_regions')->delete();
    }

}

class  UnitsTableSeeder extends Seeder
{

    public function run()
    {
        DB::table('units')->delete();
        DB::table('units')->insert(array(
            array('id' => '1',  'code' => 'PR',  'description'=> 'пара', 'symbol' => 'пар.'),
            array('id' => '2',  'code' => 'LTR', 'description'=> 'литр', 'symbol' => 'л.'),
            array('id' => '3',  'code' => 'SET', 'description'=> 'набор', 'symbol' =>   'наб.'),
            array('id' => '4',  'code' => 'RM',  'description'=> 'пачка','symbol' =>    'пач.'),
            array('id' => '5',  'code' => 'PK',  'description'=> 'упаковка','symbol' => 'упак.'),
            array('id' => '6',  'code' => 'NMP', 'description'=> 'пачок',  'symbol' =>  'пач.'),
            array('id' => '7',  'code' => 'MTR', 'description'=> 'метры', 'symbol' =>   'м'),
            array('id' => '8',  'code' => 'BX',  'description'=> 'ящик', 'symbol' => 'ящ.'),
            array('id' => '9',  'code' => 'E48', 'description'=> 'услуга', 'symbol' =>  'усл.'),
            array('id' => '10', 'code' => 'MTQ', 'description'=> 'метры кубические', 'symbol' => 'м.куб.'),
            array('id' => '11', 'code' => 'E54', 'description'=> 'рейс', 'symbol' => 'рейс'),
            array('id' => '12', 'code' => 'TNE', 'description'=> 'тонны',  'symbol' =>  'т.'),
            array('id' => '13', 'code' => 'MTK', 'description'=> 'метры квадратные', 'symbol' => 'м.кв.'),
            array('id' => '14', 'code' => 'KMT', 'description'=> 'километры',  'symbol' =>  'км'),
            array('id' => '15', 'code' => 'H87', 'description'=> 'штуки', 'symbol' =>   'шт.'),
            array('id' => '16', 'code' => 'MON', 'description'=> 'месяц',  'symbol' =>  'месяц'),
            array('id' => '17', 'code' => 'LO',  'description'=> 'лот', 'symbol' => 'лот'),
            array('id' => '18', 'code' => 'D64', 'description'=> 'блок', 'symbol' => 'блок'),
            array('id' => '19', 'code' => 'HAR', 'description'=> 'гектар', 'symbol' =>  'га.'),
            array('id' => '20', 'code' => 'KGM', 'description'=> 'килограммы', 'symbol' =>  'кг.'),
            array('id' => '25', 'code' => 'PK',  'description'=> 'упаковка', 'symbol' => 'упак.'),
            array('id' => '26', 'code' => 'PR',  'description'=> 'пара', 'symbol' => 'пар.'),
            array('id' => '27', 'code' => 'LTR', 'description'=> 'литр', 'symbol' => 'л.'),
            array('id' => '28', 'code' => 'SET', 'description'=> 'набор',  'symbol' =>  'наб.'),
            array('id' => '29', 'code' => 'RM',  'description'=> 'пачка',  'symbol' =>  'пач.'),
            array('id' => '30', 'code' => 'PK',  'description'=> 'упаковка', 'symbol' => 'упак.'),
            array('id' => '31', 'code' => 'NMP', 'description'=> 'пачок',  'symbol' =>  'пач.'),
            array('id' => '32', 'code' => 'MTR', 'description'=> 'метры', 'symbol' =>   'м'),
            array('id' => '33', 'code' => 'BX',  'description'=> 'ящик', 'symbol' => 'ящ.'),
            array('id' => '34', 'code' => 'E48', 'description'=> 'услуга', 'symbol' =>  'усл.'),
            array('id' => '35', 'code' => 'MTQ', 'description'=> 'метры кубические', 'symbol' => 'м.куб.'),
            array('id' => '36', 'code' => 'E54', 'description'=> 'рейс', 'symbol' => 'рейс'),
            array('id' => '37', 'code' => 'TNE', 'description'=> 'тонны', 'symbol' =>   'т.'),
            array('id' => '38', 'code' => 'MTK', 'description'=> 'метры квадратные', 'symbol' => 'м.кв.'),
            array('id' => '39', 'code' => 'KMT', 'description'=> 'километры', 'symbol' =>   'км'),
            array('id' => '40', 'code' => 'H87', 'description'=> 'штуки',  'symbol' =>  'шт.'),
            array('id' => '41', 'code' => 'MON', 'description'=> 'месяц', 'symbol' =>   'месяц'),
            array('id' => '42', 'code' => 'LO',  'description'=> 'лот', 'symbol' => 'лот'),
            array('id' => '43', 'code' => 'D64', 'description'=> 'блок', 'symbol' => 'блок'),
            array('id' => '44', 'code' => 'HAR', 'description'=> 'гектар', 'symbol' =>  'га.'),
            array('id' => '45', 'code' => 'KGM', 'description'=> 'килограммы', 'symbol' =>  'кг.')

        ));


    }
}

class StatusesTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('statuses')->delete();
        DB::table('statuses')->insert(array(
            array('id' => '1',  'namespace' => 'bid', 'status'=> 'registration', 'description'=> 'реєстрація',  'created_at'=>'', 'updated_at'=>''),
            array('id' => '2',  'namespace' => 'bid', 'status'=> 'validBid', 'description'=> 'дійсна пропозиція',    'created_at'=>'', 'updated_at'=>''),
            array('id' => '3',  'namespace' => 'bid', 'status'=> 'invalidBid', 'description'=>  'недійсна пропозиція',  'created_at'=>'', 'updated_at'=>''),
            array('id' => '4',  'namespace' => 'award', 'status'=>   'pending', 'description'=> 'переможець розглядається кваліфікаційною комісією',    'created_at'=>'', 'updated_at'=>''),
            array('id' => '5',  'namespace' => 'award', 'status'=>   'unsuccessful', 'description'=> 'кваліфікаційна комісія відмовила переможцю',   'created_at'=>'', 'updated_at'=>''),
            array('id' => '6',  'namespace' => 'award',  'status'=>  'active', 'description'=>  'закупівлю виграв учасник з пропозицією bid_id',    'created_at'=>'', 'updated_at'=>''),
            array('id' => '7',  'namespace' => 'award',  'status'=>  'cancelled',  'description'=>  'орган, що розглядає скарги, відмінив результати закупівлі',    'created_at'=>'', 'updated_at'=>''),
            array('id' => '8',  'namespace' => 'complaint', 'status'=>   'pending', 'description'=> 'не вирішено, ще обробляється', 'created_at'=>'', 'updated_at'=>''),
            array('id' => '9',  'namespace' => 'complaint',  'status'=>  'invalid', 'description'=> 'недійсно', 'created_at'=>'', 'updated_at'=>''),
            array('id' => '10', 'namespace' => 'complaint', 'status'=>   'declined','description'=> 'відхилено',    'created_at'=>'', 'updated_at'=>''),
            array('id' => '11', 'namespace' => 'complaint', 'status'=>   'resolved', 'description'=> 'вирішено', 'created_at'=>'', 'updated_at'=>''),
            array('id' => '12', 'namespace' => 'contract', 'status'=> 'pending', 'description'=> 'цей договір запропоновано, але він ще не діє. Можливо очікується його підписання.',    'created_at'=>'', 'updated_at'=>''),
            array('id' => '13', 'namespace' => 'contract', 'status'=> 'active', 'description'=>  'цей договір підписаний всіма учасниками, і зараз діє на законних підставах.',  'created_at'=>'', 'updated_at'=>''),
            array('id' => '14', 'namespace' => 'contract', 'status'=> 'cancelled',  'description'=>  'цей договір було скасовано до підписання.',    'created_at'=>'', 'updated_at'=>''),
            array('id' => '15', 'namespace' => 'contract', 'status'=> 'terminated', 'description'=>  'цей договір був підписаний та діяв, але вже завершився. Це може бути пов’язано з виконанням договору, або з достроковим припиненням через якусь незавершеність.',  'created_at'=>'', 'updated_at'=>''),
            array('id' => '16', 'namespace' => 'cancellation', 'status'=> 'pending', 'description'=> 'Стандартно. Запит оформляється.',  'created_at'=>'', 'updated_at'=>''),
            array('id' => '17', 'namespace' => 'cancellation',  'status'=> 'active', 'description'=> 'Скасування активоване.',   'created_at'=>'', 'updated_at'=>''),
            array('id' => '18', 'namespace' => 'tender', 'status'=>  'active.enquiries', 'description'=> 'Період уточнень',  'created_at'=>'', 'updated_at'=>''),
            array('id' => '19', 'namespace' => 'tender', 'status'=>  'active.tendering', 'description'=> 'Очікування пропозицій',    'created_at'=>'', 'updated_at'=>''),
            array('id' => '20', 'namespace' => 'tender', 'status'=>  'active.auction', 'description'=>  'Період аукціону',  'created_at'=>'', 'updated_at'=>''),
            array('id' => '21', 'namespace' => 'tender', 'status'=>  'active.qualification','description'=> 'Кваліфікація переможця',   'created_at'=>'', 'updated_at'=>''),
            array('id' => '22', 'namespace' => 'tender', 'status'=>  'active.awarded', 'description'=>  'Пропозиції розглянуто (розглянуто)',   'created_at'=>'', 'updated_at'=>''),
            array('id' => '23', 'namespace' => 'tender',  'status'=> 'unsuccessful','description'=> 'Закупівля не відбулась ( не відбулась)',    'created_at'=>'', 'updated_at'=>''),
            array('id' => '24', 'namespace' => 'tender', 'status'=>  'complete','description'=> 'Завершена закупівля (завершена)',  'created_at'=>'', 'updated_at'=>''),
            array('id' => '25', 'namespace' => 'tender',  'status'=> 'cancelled', 'description'=>   'Відмінена закупівля',  'created_at'=>'', 'updated_at'=>''),
            array('id' => '26', 'namespace' =>  'tender',   'status'=>'draft',   'description'=> 'Черновик', 'created_at'=>'', 'updated_at'=>'')
        ));


    }

}

class DocumentTypeTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('document_types')->delete();
        DB::table('document_types')->insert(array(
            array('id' => '1', 'namespace' => 'tender',        'document_type' =>   'notice',    'lang_ua' =>'Повідомлення про закупівлю',    'lang_ru'=> NULL, 'created_at'=>   '0000-00-00 00:00:00', 'updated_at'=>  '0000-00-00 00:00:00'),
            array('id' => '2', 'namespace' => 'tender',        'document_type' =>   'biddingDocuments',  'lang_ua' =>'Документи закупівлі',   'lang_ru'=> NULL, 'created_at'=>   '0000-00-00 00:00:00', 'updated_at'=>  '0000-00-00 00:00:00'),
            array('id' => '3', 'namespace' => 'tender',        'document_type' =>   'technicalSpecifications',  'lang_ua' => 'Технічні специфікації',     'lang_ru'=> NULL, 'created_at'=>   '0000-00-00 00:00:00', 'updated_at'=>  '0000-00-00 00:00:00'),
            array('id' => '4', 'namespace' => 'tender',        'document_type' =>   'evaluationCriteria',   'lang_ua' => 'Критерії оцінки',   'lang_ru'=> NULL, 'created_at'=>   '0000-00-00 00:00:00', 'updated_at'=>  '0000-00-00 00:00:00'),
            array('id' => '5', 'namespace' => 'tender',        'document_type' =>   'clarifications',   'lang_ua' => 'Пояснення до питань заданих учасниками',    'lang_ru'=> NULL, 'created_at'=>   '0000-00-00 00:00:00', 'updated_at'=>  '0000-00-00 00:00:00'),
            array('id' => '6', 'namespace' => 'tender',        'document_type' =>   'eligibilityCriteria',   'lang_ua' =>'Критерії прийнятності',     'lang_ru'=> NULL, 'created_at'=>   '0000-00-00 00:00:00', 'updated_at'=>  '0000-00-00 00:00:00'),
            array('id' => '7', 'namespace' => 'tender',        'document_type' =>   'shortlistedFirms', 'lang_ua' => 'Фірми у короткому списку',  'lang_ru'=> NULL, 'created_at'=>   '0000-00-00 00:00:00', 'updated_at'=>  '0000-00-00 00:00:00'),
            array('id' => '8', 'namespace' => 'tender',        'document_type' =>   'riskProvisions',    'lang_ua' =>'Положення для управління ризиками та зобов’язаннями',   'lang_ru'=> NULL, 'created_at'=>   '0000-00-00 00:00:00', 'updated_at'=>  '0000-00-00 00:00:00'),
            array('id' => '9', 'namespace' => 'tender',        'document_type' =>   'billOfQuantity',   'lang_ua' => 'Кошторис',  'lang_ru'=> NULL, 'created_at'=>   '0000-00-00 00:00:00', 'updated_at'=>  '0000-00-00 00:00:00'),
            array('id' => '10',  'namespace' =>  'tender',     'document_type' =>   'bidders', 'lang_ua' =>  'Інформація про учасників',  'lang_ru'=> NULL, 'created_at'=>   '0000-00-00 00:00:00', 'updated_at'=>  '0000-00-00 00:00:00'),
            array('id' => '11',  'namespace' =>  'tender',     'document_type' =>   'conflictOfInterest',  'lang_ua' =>  'Виявлені конфлікти інтересів',  'lang_ru'=> NULL, 'created_at'=>   '0000-00-00 00:00:00', 'updated_at'=>  '0000-00-00 00:00:00'),
            array('id' => '12',  'namespace' =>  'tender',     'document_type' =>     'debarments',  'lang_ua' =>  'Недопущення до закупівлі',  'lang_ru'=> NULL, 'created_at'=>   '0000-00-00 00:00:00', 'updated_at'=>  '0000-00-00 00:00:00'),
            array('id' => '13',  'namespace' =>  'award',      'document_type' => 'notice',   'lang_ua' => 'Повідомлення про рішення',  'lang_ru'=> NULL, 'created_at'=>   '0000-00-00 00:00:00', 'updated_at'=>  '0000-00-00 00:00:00'),
            array('id' => '14',  'namespace' =>  'award',      'document_type' => 'evaluationReports',   'lang_ua' =>  'Звіт про оцінку',   'lang_ru'=> NULL, 'created_at'=>   '0000-00-00 00:00:00', 'updated_at'=>  '0000-00-00 00:00:00'),
            array('id' => '15',  'namespace' =>  'award',      'document_type' => 'winningBid',   'lang_ua' => 'Пропозиція, що перемогла',  'lang_ru'=> NULL, 'created_at'=>   '0000-00-00 00:00:00', 'updated_at'=>  '0000-00-00 00:00:00'),
            array('id' => '16',  'namespace' =>  'award',      'document_type' => 'complaints',  'lang_ua' =>  'Скарги та рішення',     'lang_ru'=> NULL, 'created_at'=>   '0000-00-00 00:00:00', 'updated_at'=>  '0000-00-00 00:00:00'),
            array('id' => '17',  'namespace' =>  'contract',   'document_type' =>   'notice',   'lang_ua' => 'Повідомлення про договір',  'lang_ru'=> NULL, 'created_at'=>   '0000-00-00 00:00:00', 'updated_at'=>  '0000-00-00 00:00:00'),
            array('id' => '18',  'namespace' =>  'contract',   'document_type' =>   '  contractSigned',   'lang_ua' => 'Підписаний договір',    'lang_ru'=> NULL, 'created_at'=>   '0000-00-00 00:00:00', 'updated_at'=>  '0000-00-00 00:00:00'),
            array('id' => '19',   'namespace' =>  'contract',  'document_type' =>  'contractArrangements', 'lang_ua' => 'Заходи для припинення договору',    'lang_ru'=> NULL, 'created_at'=>   '0000-00-00 00:00:00', 'updated_at'=>  '0000-00-00 00:00:00'),
            array('id' => '20',  'namespace' =>   'contract',  'document_type' =>  'contractSchedule', 'lang_ua' => 'Розклад та етапи',  'lang_ru'=> NULL, 'created_at'=>   '0000-00-00 00:00:00', 'updated_at'=>  '0000-00-00 00:00:00'),
            array('id' => '21',  'namespace' =>   'contract',  'document_type' =>  'contractAnnexe',   'lang_ua' => 'Додатки до договору',   'lang_ru'=> NULL, 'created_at'=>   '0000-00-00 00:00:00', 'updated_at'=>  '0000-00-00 00:00:00'),
            array('id' => '22',  'namespace' =>   'contract',  'document_type' =>  'contractGuarantees',  'lang_ua' =>  'Гарантії',  'lang_ru'=> NULL, 'created_at'=>   '0000-00-00 00:00:00', 'updated_at'=>  '0000-00-00 00:00:00'),
            array('id' => '23',  'namespace' =>   'contract',  'document_type' =>  'subContract',  'lang_ua' => 'Субпідряд',     'lang_ru'=> NULL, 'created_at'=>   '0000-00-00 00:00:00', 'updated_at'=>  '0000-00-00 00:00:00')
        ));


    }

}

class CurrenciesTypeTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('currencies')->delete();
        DB::table('currencies')->insert(array(
            array('id' => '1', 'currency_code' => 'UAH', 'currency_description' => 'Грн.'),
            array('id' => '2', 'currency_code' => 'USD', 'currency_description' => 'Долларов США'),
            array('id' => '3', 'currency_code' => 'EUR', 'currency_description' => 'Евро')
        ));


    }

}

