<?php

namespace App\Console\Commands;

use App\Model\Feature;
use App\Model\FeatureValue;
use App\Model\Item;
use App\Model\Lot;
use App\Model\Organization;
use App\Model\Tender;
use Event;
use Faker\Factory as Faker;
use Illuminate\Console\Command;

class AddTender extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'add {id=4}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $faker = Faker::create('ru_RU');
        $id = $this->argument('id');
        $organization = Organization::find($id);
        $amount = $faker->numberBetween(10000, 100000);
        $tenderData = [
            'title' => ucfirst($faker->word).' '.$faker->word,
            'title_en' => ucfirst($faker->word).' '.$faker->word,
            'description' => $faker->realText(),
            'description_en' => $faker->realText(),
            'source' => 1,
            'multilot' => 1,
            'type_id' => 1,
            'mode' => 0,
            'status' => 'draft',
            'amount' => $amount,
            'currency_id' => 1,
            'tax_included' => 1,
            'minimal_step' => round($amount * 0.1),
            'enquiry_start_date' => date('Y-m-d H:i:s'),
            'enquiry_end_date' => date('Y-m-d H:i:s', time() + 2 * 60),
            'tender_start_date' => date('Y-m-d H:i:s', time() + 3 * 60),
            'tender_end_date' => date('Y-m-d H:i:s', time() + 5 * 60),
            'contact_phone' => $organization->contact_phone,
            'contact_url' => $organization->contact_url,
            'contact_name' => $organization->contact_name,
            'contact_name_en' => $organization->contact_name,
            'contact_email' => $organization->contact_email
        ];
        $tender = new Tender($tenderData);
        $organization->tenders()->save($tender);
        for ($i = 1; $i < rand(2,3); $i++ ) {
            $featureData = [];
            $featureData['title'] = ucfirst($faker->word).' '.$faker->word;
            $featureData['title_en'] = ucfirst($faker->word).' '.$faker->word;
            $featureData['description'] = ucfirst($faker->word).' '.$faker->word;
            $featureData['tender_id'] = $tender->id;
            $feature = new Feature($featureData);
            $tender->features()->save($feature);
            $featureValueData = [];
            for ($j = 1; $j < rand(2,4); $j++) {
                $featureValueData[] = [
                    'title' => ucfirst($faker->word).' '.$faker->word,
                    'value' => $j
                ];
            }
            $featureValueData[] = [
                'title' => ucfirst($faker->word).' '.$faker->word,
                'value' => 0
            ];
            foreach ($featureValueData as $fData) {
                $featureValue = new FeatureValue($fData);
                $feature->values()->save($featureValue);
            }


        }
        $codes = [rand(11073, 20526), rand(1, 11072)];
        for ($i = 0; $i < rand(3,3); $i++) {
            $lotData = [
                'title' => ucfirst($faker->word).' '.$faker->word,
                'title_en' => ucfirst($faker->word).' '.$faker->word,
                'description' => $faker->realText,
                'description_en' => $faker->realText,
                'amount' => $amount,
                'minimal_step' => round($amount * 0.01)
            ];
            $lot = new Lot($lotData);
            $tender->lots()->save($lot);
            for ($j = 0; $j < rand(1,1); $j++) {
                $itemData = [
                    "description" => $faker->realText,
                    "description_en" => $faker->realText,
                    "quantity" => $faker->numberBetween(1, 10),
                    "unit_id" => $faker->numberBetween(1, 24),
                    'delivery_date_start' => date('Y-m-d H:i:s'),
                    'delivery_date_end' => date('Y-m-d H:i:s', time() + 86400 * 4),
                    'region_id' => 3,
                    'country_id' => 1,
                    'postal_code' => '45343',
                    'locality' => 'Киев',
                    'delivery_address' => ucfirst($faker->word).' '.$faker->word,
                    'same_delivery_address' => 0
                ];
                $item = new Item($itemData);
                $lot->items()->save($item);
                $item->codes()->sync($codes);
            }
        }
//        Event::fire(new TenderSaveEvent($tender));
    }
}
