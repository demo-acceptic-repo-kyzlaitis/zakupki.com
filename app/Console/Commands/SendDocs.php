<?php

namespace App\Console\Commands;

use Illuminate\Console\Command,
    App\Model\Document,
    App\Api\Struct\Document as Structure,
    App\Api\Api,
    Carbon\Carbon;

class SendDocs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'docs:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description.';

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
        $date = Carbon::now()->subHours(1)->format('Y-m-d H:i:s');
        $documents = Document::where('url', '')->where('created_at', '<', $date)->get();
        $api = new Api();
        foreach ($documents as $document) {
            $documentStruct = new Structure($document);
            $response = $api->upload($documentStruct);

            $document->date_published = Carbon::parse($response['data']['datePublished'])->format('Y-m-d H:i:s');
            $document->date_modified = Carbon::parse($response['data']['dateModified'])->format('Y-m-d H:i:s');
            $document->format = $response['data']['format'];
            $document->orig_id = $response['data']['id'];
            $document->title = $response['data']['title'];
            $document->url = $response['data']['url'];
            $document->save();

        }
    }
}
