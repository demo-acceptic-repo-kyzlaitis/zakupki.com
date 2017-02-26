<?php

namespace App\Jobs;

use App\Api\Api;
use App\Api\Struct\Document;
use App\Jobs\Job;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

class UploadDocument extends Job implements SelfHandling, ShouldQueue
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    
    public $documentStructure;
    
    public function __construct($documentStructure)
    {
        $this->documentStructure = $documentStructure;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $api = new Api();
        $api->upload($this->documentStructure);
    }
}
