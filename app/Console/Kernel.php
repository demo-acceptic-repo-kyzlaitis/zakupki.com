<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\Inspire::class,
        \App\Console\Commands\SendDocs::class,
        \App\Console\Commands\TendersGetQuestions::class,
        \App\Console\Commands\TendersSync::class,
        \App\Console\Commands\TendersPost::class,
        \App\Console\Commands\Sync::class,
        \App\Console\Commands\SyncContracts::class,
        \App\Console\Commands\SyncNew::class,
        \App\Console\Commands\SyncOur::class,
        \App\Console\Commands\SyncPlans::class,
        \App\Console\Commands\UploadDocs::class,
        \App\Console\Commands\AddTender::class,
        \App\Console\Commands\UpdatePostalCodes::class,
        \App\Console\Commands\Classifiers::class,
        \App\Console\Commands\Notify::class,
        \App\Console\Commands\Export::class,
        \App\Console\Commands\NotifyBeforeAuction::class,
        \App\Console\Commands\SendPersonalEmail::class,
        \App\Console\Commands\GetContractCredentials::class,
        \App\Console\Commands\ExportsSync::class,
        \App\Console\Commands\ExportsSend::class,
        \App\Console\Commands\SyncBids::class,
        \App\Console\Commands\GetContractsAccessToken::class,
        \App\Console\Commands\BidsLink::class,
        \App\Console\Commands\UpdateIdentifiers::class,
        \App\Console\Commands\UpdateOrganizationIdentifiers::class,
        \App\Console\Commands\UpdatePlanMode::class,
        \App\Console\Commands\TenderAgent::class,
        \App\Console\Commands\GenerateHashForOurOrganizations::Class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
//        $schedule->command('sync')->withoutOverlapping()->everyMinute();
        $schedule->command('bids:link')->withoutOverlapping()->everyTenMinutes();
//        $schedule->command('sync:new')->withoutOverlapping()->everyMinute()->appendOutputTo(storage_path('logs').'/syncnew.log');
        $schedule->command('sync:contract')->withoutOverlapping()->hourly();
        $schedule->command('sync:our')->withoutOverlapping()->everyFiveMinutes();
        $schedule->command('sync:bids')->withoutOverlapping()->hourly();
        $schedule->command('notify:all')->withoutOverlapping()->everyMinute();
        $schedule->command('classifiers')->withoutOverlapping()->dailyAt('07:00');
        $schedule->command('agent')->withoutOverlapping()->dailyAt('10:00');
        //$schedule->command('notify:auction')->withoutOverlapping()->everyMinute();
        if (env('APP_ENV') == 'server') {
	        $schedule->command('exports:sync')->withoutOverlapping()->everyTenMinutes();
            $schedule->command('export')->withoutOverlapping()->everyFiveMinutes();
//            $schedule->command('sync')->withoutOverlapping()->cron('0 */3 * * *');
        }
    }
}
