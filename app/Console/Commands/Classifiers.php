<?php

namespace App\Console\Commands;

use App\Model\Codes;
use App\Model\Units;
use Illuminate\Console\Command;

class Classifiers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'classifiers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update classifiers';

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
//        $classifiers = \App\Model\Classifiers::get();
//        foreach ($classifiers as $classifier) {
//            if (!empty($classifier->url)) {
//                $_data = json_decode(file_get_contents($classifier->url));
//                foreach ($_data as $code => $description) {
//                    $codeModel = Codes::where('code', $code)->where('type', $classifier->id)->first();
//                    if ($codeModel) {
//                        $codeModel->update(['description' => $description]);
//                    } else {
//                        $codeModel = new Codes([
//                            'code' => $code,
//                            'type' => $classifier->id,
//                            'parent_id' => 0,
//                            'description' => $description
//                        ]);
//                        $codeModel->save();
//                    }
//                }
//            }
//        }

        $_data = json_decode(file_get_contents('http://standards.openprocurement.org/unit_codes/recommended/uk.json'), true);
        foreach ($_data as $code => $description) {
            $unitModel = Units::where('code', $code)->first();
            if ($unitModel) {
                $unitModel->update(['description' => $description['name_uk'], 'symbol' => $description['symbol_uk']]);
            } else {
                $unitModel = new Units([
                    'code' => $code,
                    'description' => $description['name_uk'],
                    'symbol' => $description['symbol_uk']
                ]);
                $unitModel->save();
            }
        }

    }
}
