<?php

namespace App\Console\Commands;

use App\Model\Notification;
use App\Model\User;
use Carbon\Carbon;
use Event;
use App\Events\TenderSaveEvent;
use App\Model\Item;
use App\Model\Lot;
use App\Model\Organization;
use App\Model\Tender;
use Illuminate\Console\Command;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Mail;

class SendPersonalEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sendEmailTo {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends email from notification list';

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
        $email = $this->argument('email');
        Mail::send('emails.personal', [], function($message) use($email) {
            $message->to($email, '')->subject('Дата аукціону');
        });


    }
}
