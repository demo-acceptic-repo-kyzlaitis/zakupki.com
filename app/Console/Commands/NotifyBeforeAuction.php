<?php

namespace App\Console\Commands;


use App\Helpers\Mailable;
use App\Model\Bid;
use App\Model\Notification;
use App\Model\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Support\Facades\Mail;

class NotifyBeforeAuction extends Command
{

    protected $signature = 'notify:auction';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'За пол часа до аукциона высылает
                                уведомление о том что аукцион скоро начнется';


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
        $notifications = Notification::where('alias', 'tender.auction.start')->get();

        foreach ($notifications as $notification) {
            $user   = User::findOrFail($notification->user_id);
            $entity = Bid::findOrFail($notification->notificationable_id)->bidable()->getResults();

            $startDate         = new Carbon($entity->auction_start_date);
            $now                = (new Carbon())->now();
            $notificationUpdate = new Carbon($notification->updated_at);

            $diff       = $startDate->diffInMinutes($now);
            $updateDiff = $notificationUpdate->diffInMinutes($now);

            if ($diff <= 30 && !($updateDiff <= 30)) {

                $notification->touch();
                if($user->super_user == 1) {
                    Mail::send('emails.auctionReminder', compact('notification'), function ($message) use ($user, $notification) {
                        $message->to($user->email, $user->name)->subject($notification->title);
                    });
                } else {
                    $mailable = new Mailable();

                    $mailable->message['to'] = [['email' => $user->email]];

                    $mailable->message['merge_vars'][0]['vars'][] = [
                        'name'    => 'notificationText',
                        'content' => $notification->text,
                    ];

                    $mailable->message['merge_vars'][0]['rcpt'] = $user->email;
                    $mailable->message['subject']               = $notification->title;

                    $mandrillEmail = new \Weblee\Mandrill\Mail(env('MANDRILL_SECRET'));
                    $mandrillEmail->messages()->sendTemplate('notify', $mailable->template_content, $mailable->message);

                    $mailable = null;
                }
            }
        }
    }
}
