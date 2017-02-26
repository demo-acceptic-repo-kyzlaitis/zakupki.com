<?php

namespace App\Jobs;

use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class WriteLogFile extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $_logger;
    public $log;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($log)
    {
        $this->log = $log;
//        $this->_logger = new Logger('Actions');
//        $this->_logger->pushHandler(new StreamHandler('storage/logs/'.date('Y-m-d').'.log'));

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
//        $this->_logger->info($this->log['session_id']."\t".$this->log['user_id']."\t".$this->log['method']."\t".$this->log['route']."\t".$this->log['url']."\t".print_r($this->log['params'], true));
    }
}
