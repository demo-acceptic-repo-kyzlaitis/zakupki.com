<?php

namespace App\Listeners;

use App\Events\UserRegister;
use App\Events\UserRegisterEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail as LaravelMail;
use Weblee\Mandrill\Mail;

class UserRegisterListener
{
    /**
     * Create the event listener.
     *
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  UserRegisterEvent  $event
     * @return void
     */
    public function handle(UserRegisterEvent $event)
    {
        $user = $event->user;
        //if (env('APP_ENV') == 'server') {
            $message = array(
                'subject' => 'Підтвердіть Ваш e-mail', //тема письма (тошо наверху)
                'from_email' => 'no-reply@zakupki.com.ua', //то как будет выглядить у юзера "от кого письмо"
                'html' => '<p>this is a test message with Mandrill\'s PHP wrapper!.</p>', //нашиг не нужно
                'to' => [
                    ['email' => $user->email, 'name' => 'Recipient 1']
                ], //важен только емайл, нейм это херь лишняя
                'merge_vars' => [
                    [
                        'rcpt' => $user->email,
                        'vars' => [
                            [
                                'name'    => 'userEmail',
                                // название переменной в шаблоне письма мандрила
                                'content' => $user->email,
                                // то что подставить в шаблон
                            ],
                            [
                                'name'    => 'linkToTheWebSite',
                                // название переменной в шаблоне письма мандрила
                                'content' => env('BASE_URL'),
                                // то что подставить в шаблон
                            ],
                            [
                                'name'    => 'activation_code',
                                // название переменной в шаблоне письма мандрила
                                'content' => $user->activation_code,
                                // то что подставить в шаблон
                            ],
                            [
                                'name'    => 'link',
                                // название переменной в шаблоне письма мандрила
                                'content' => env('BASE_URL') . route('user.activate', $user->activation_code, false),
                                // то что подставить в шаблон
                            ],
                            [
                                'name'    => 'textInsideLink',
                                // название переменной в шаблоне письма мандрила
                                'content' => env('BASE_URL') . route('user.activate', $user->activation_code, false),
                                // то что подставить в шаблон
                            ],
                        ],
                    ]
                ],
                'merge_language' => 'handlebars'
            );

            $template_content = array(
                array(
                    'name' => 'main',
                    'content' => 'Hi *|FIRSTNAME|* *|LASTNAME|*, thanks for signing up.'),
                array(
                    'name' => 'footer',
                    'content' => 'Copyright 2012.')

            );

            $asd = new Mail(env('MANDRILL_SECRET'));
            $asd->messages()->sendTemplate('emails-activate', $template_content, $message);

        //} else {
//            LaravelMail::queue('emails.activate', ['user' => $user], function ($message) use ($user) {
//                $message->to($user->email, $user->name)->subject('Підтвердіть Ваш e-mail');
//            });
        //}




    }
}
