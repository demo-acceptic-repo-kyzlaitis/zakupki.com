<?php

namespace App\Console\Commands;

use App\Model\PostalCode;
use App\Model\TendersRegions;
use Illuminate\Console\Command;

class UpdatePostalCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'postalcodes';

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
        $file = fopen('postal_codes_of_ukraine.csv', 'r');
        while (!feof($file)) {
            $line = fgetcsv($file, null, ';');
            $region = TendersRegions::where('region_search', 'LIKE', "%{$line[0]}%")->first();
            if ($region) {
                $postal = new PostalCode([
                    'postal_code' => $line[1],
                    'region_id' => $region->id
                ]);
                $postal->save();
            }
        }
    }
}
