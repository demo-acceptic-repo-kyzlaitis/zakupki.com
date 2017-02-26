<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Model\Organization;
use Illuminate\Contracts\Bus\SelfHandling;

class GenerateHashForOurOrganizations extends Command implements SelfHandling
{

    /**
     * @description generates for our organization based on identifier(эдрпоу), address and organization name
     *
     */
    protected $signature = 'genhash';


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
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $organizations = Organization::where('user_id', '>', '0')->where('source', '!=', 2)->get();

        foreach($organizations as $organization ) {
            $dataForHash = [];

            $dataForHash[] = $organization->identifier;
            $dataForHash[] = $organization->name;
            $dataForHash[] = $organization->getAddress();

            $organizationHash = hashFromArray($dataForHash);

            $organization->update(['ina_id' => $organizationHash]);
        }
    }
}
