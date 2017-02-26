<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

class DBController extends Controller
{
    protected function showQueue() {
        $queue = [];
        if (env('QUEUE_DRIVER') == 'database') {
            $queue['default'] = count(DB::table('jobs')->where('queue', 'default')->get());
            $queue['tenders'] = count(DB::table('jobs')->where('queue', 'tenders')->get());
            $queue['bids'] = count(DB::table('jobs')->where('queue', 'bids')->get());
        } elseif (env('QUEUE_DRIVER') == 'redis') {
            $queue['default'] = (Queue::getRedis()->command('LLEN',['queues:default']));
            $queue['tenders'] = (Queue::getRedis()->command('LLEN',['queues:default:tenders']));
            $queue['bids'] = (Queue::getRedis()->command('LLEN',['queues:default:bids']));
        }

        return view('admin.pages.db.queue', compact('queue'));
    }
}
