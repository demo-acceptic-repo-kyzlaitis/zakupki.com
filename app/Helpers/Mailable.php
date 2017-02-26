<?php

namespace App\Helpers;
/**
 * User: illia
 * Date: 8/22/2016
 * Time: 4:10 AM
 */
class Mailable {

    /**
     * https://mandrill.zendesk.com/hc/en-us/articles/205582537 тут документация по языку разметри который стоит для
     * всех письм
     *
     * @var array
     */
    public $message;

    public $template_content;

    public function __construct() {
        $this->message = [
            'subject'        => '', //тема письма (тошо наверху)
            'from_email'     => 'no-reply@zakupki.com.ua', //то как будет выглядить у юзера "от кого письмо"
            'from_name'      => 'Zakupki.com.ua',
            'html'           => '<p>this is a test message with Mandrill\'s PHP wrapper!.</p>', //наших не нужно
            'to'             => [
                [
                    'email' => '', //важен только емайл, нейм это херь лишняя
                    'name'  => 'Recipient 1',
                ],
            ],
            'merge_vars'     => [
                [
                    'rcpt' => '',
                    'vars' => [
                        [
                            'name'    => 'unsubscribe',
                            'content' => "Щоб відмовитись від розсилки по Вашим закупівлям, будь ласка, перейдіть за <a href=". env('BASE_URL') . "/notification/unsubscribe" . ">посиланням.</a>",
                        ]
                    ],
                ],
            ],
            'merge_language' => 'handlebars',
            "metadata"       => [
                "website" => "Zakupki.com.ua",
            ],


        ]; //end of message array

        $this->template_content = [
            [
                'name'    => 'main',
                'content' => 'Hi *|FIRSTNAME|* *|LASTNAME|*, thanks for signing up.',
            ],
            [
                'name'    => 'footer',
                'content' => 'Copyright 2012.',
            ],

        ];
    }
}