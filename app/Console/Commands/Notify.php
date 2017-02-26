<?php

namespace App\Console\Commands;

use App\Helpers\Mailable;
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
use Illuminate\Support\Facades\Mail as LaravelMail;

class Notify extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:all';

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
    public function __construct() {
        parent::__construct();
    }

    /**
     *
     *
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $notifications = Notification::where('status', '!=', 'log')->where('sended_at', null)->where('alias', 'not like', 'custom.notification')->get();

        foreach ($notifications as $notification) {
            //не разобрался как у нотификации исчез юзер-айди он должен быть
            // TODO когда будет доступ к бд проду разобраться
            $user = User::find($notification->user_id);
            if($user != null) {
                if($user->subscribe != 0) {
                    if($user->super_user == 1) {
                        LaravelMail::send('emails.notify', compact('notification'), function ($message) use ($user, $notification) {
                            $message->to($user->email, $user->name)->subject($notification->title);
                        });
                    } else {
                        $mailable                                     = new Mailable();
                        $mailable->message['to']                      = [['email' => $user->email]];
                        $mailable->message['merge_vars'][0]['vars'][] = [
                            'name'    => 'notificationText',
                            'content' => $notification->text,
                        ];
                        $mailable->message['merge_vars'][0]['rcpt']   = $user->email;
                        $mailable->message['subject']                 = $notification->title;

                        $mandrillMail = new \Weblee\Mandrill\Mail(env('MANDRILL_SECRET'));
                        $mandrillMail->messages()->sendTemplate('notify', $mailable->template_content, $mailable->message);

                        $mailable = null; //gc will collect it later
                    }
                    $notification->update(['sended_at' => Carbon::now()]);
                }
            }
        }
    }
}
