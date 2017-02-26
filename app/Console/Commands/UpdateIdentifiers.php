<?php

namespace App\Console\Commands;

use App\Model\Country;
use App\Model\Identifier;
use Illuminate\Console\Command;

class UpdateIdentifiers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updateIdentifiers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update identifiers';

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
        $url = 'http://standards.openprocurement.org/codelists/organization-identifier-scheme/en.json';
        $arIdentifiers = json_decode(file_get_contents($url), true);

        foreach ($arIdentifiers['data'] as $val) {
            $identifierModel = Identifier::where('scheme', $val['code'])->first();
            $identifierData = [
                'country_iso' => $val['category'],
                'scheme' => $val['code'],
                'name' => $val['name'],
                'description' => $val['description'],
                'uri' => $val['url'],
                'status' => ($val['public-database']) ? 1 : 0
            ];
            if ($identifierModel) {
                $identifierModel->update($identifierData);
            } else {
                $identifierModel = new Identifier($identifierData);
                $identifierModel->save();
            }
        }

        Country::where('country_status', '1')->update(['country_status' => '0']);
        Country::select('country.id', 'country.country_iso')->join('identifiers', 'country.country_iso', '=', 'identifiers.country_iso')->where('identifiers.status', 1)->groupBy('identifiers.country_iso')->update(['country_status' => '1']);
    }
}
