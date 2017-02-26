<?php

namespace App\Console\Commands;

use App\Model\Country;
use App\Model\Identifier;
use App\Model\Plan;
use Illuminate\Console\Command;

class UpdatePlanMode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updatePlanMode';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update plan mode';

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
        $plans = Plan::where('cbd_id', '!=', '')->get();
        $url = env('PRZ_API_PUBLIC') . '/plans/';
        foreach ($plans as $plan) {
            $planData = json_decode(file_get_contents($url . $plan->cbd_id), true);
            if (isset($planData['data'])) {
                $plan->mode = (isset($planData['data']['mode']) && $planData['data']['mode'] == 'test') ? 0 : 1;
                if (!$plan->mode && strpos($plan->description, '[ТЕСТУВАННЯ]') === false)
                    $plan->description = '[ТЕСТУВАННЯ] ' . $plan->description;

                $plan->update();
            }
        }
    }
}
