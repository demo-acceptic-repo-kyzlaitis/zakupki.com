<?php

namespace App\Console\Commands;

use App\Model\Country;
use App\Model\Identifier;
use App\Model\IdentifierOrganization;
use App\Model\Organization;
use Illuminate\Console\Command;

class UpdateOrganizationIdentifiers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updateOrganizationIdentifiers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update organization identifiers';

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
        $organizations = Organization::where('identifier', '!=', 'null')->get();
        $scheme = Identifier::where('scheme', 'UA-EDR')->first();
        $country = Country::where('country_iso', 'UA')->first();
        if ($scheme && $country) {
            foreach ($organizations as $organization) {
                $organizationIdentifiers = IdentifierOrganization::where('organisation_id', $organization->id)
                    ->where('identifier_id', $scheme->id)->first();
                if ($organizationIdentifiers) {
                    $organizationIdentifiers->update(['identifier' => $organization->identifierNum]);
                } else {
                    $organizationIdentifiers = new IdentifierOrganization([
                        'organisation_id' => $organization->id,
                        'identifier_id' => $scheme->id,
                        'identifier' => $organization->identifierNum
                    ]);
                    $organizationIdentifiers->save();
                }

                $organization->update([
                    'country_id' => $country->id,
                    'identifier' => null,
                ]);
            }
        }
        echo 'Count: ' . $organizations->count() . ' ';
    }
}
