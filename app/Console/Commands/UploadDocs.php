<?php

namespace App\Console\Commands;

use App\Api\Api;
use App\Model\Document;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UploadDocs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upload';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $documents = DB::table('documents')
            ->join('contactssave', 'users.id', '=', 'contacts.user_id')
            ->join('orders', 'users.id', '=', 'orders.user_id')
            ->select('users.*', 'contacts.phone', 'orders.price')
            ->get();

        dd($documents->count());
        $document = Document::find(696);
        $api = new Api();
        $documentStructure = new \App\Api\Struct\Document($document);
        $response = $api->upload($documentStructure);

        $document->date_published = Carbon::parse($response['data']['datePublished'])->format('Y-m-d H:i:s');
        $document->date_modified = Carbon::parse($response['data']['dateModified'])->format('Y-m-d H:i:s');
        $document->format = $response['data']['format'];
        $document->orig_id = $response['data']['id'];
        $document->title = $response['data']['title'];
        $document->url = $response['data']['url'];
        $document->save();

        $document = Document::find(696);
        $documentStructure = new \App\Api\Struct\Document($document);
        $response = $api->patch($documentStructure);

    }
}
