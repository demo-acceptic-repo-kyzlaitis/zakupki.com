<?php

namespace App\Http\Middleware;

use App\Jobs\WriteLogFile;
use Closure;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Auth;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;

class Log
{
    use DispatchesJobs;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

    public function getallheaders()
    {
        $headers = [];
        foreach ($_SERVER as $name => $value)
        {
            if (substr($name, 0, 5) == 'HTTP_')
            {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }

    public function handle($request, Closure $next)
    {
        $logger = new Logger('HTTP');
        $logger->pushHandler(new RotatingFileHandler(storage_path('logs/HTTP.log')));
        if ($_SERVER['REQUEST_URI'] != '/keep-alive') {
            $data = [
                'URL' => $_SERVER['REQUEST_URI'],
                'params' => $request->except('password', '_token', 'files'),
                'method' => $_SERVER['REQUEST_METHOD'],
                'client_id' => $_SERVER['REMOTE_ADDR'],
            ];
            if (isset($_COOKIE["laravel_session"])) {
                $data['session_id'] = $_COOKIE["laravel_session"];
            }
            if (Auth::check()) {
                $data['user_id'] = Auth::user()->id;
            }

            $data['headers'] = $this->getallheaders();
            $data['files'] = $_FILES;

            $logger->info(json_encode($data));
        }

        return $next($request);
    }

//    public function handle($request, Closure $next)
//    {
//        $log['session_id'] = $request->session()->getId();
//        $log['user_id'] =  !is_null(Auth::user()) ? Auth::user()->id : null;
//        $log['method'] = $request->getMethod();
//        $log['route'] = $request->route()->getName();
//        $log['url'] = $request->getRequestUri();
//        $log['params'] = $request->except('password', '_token', 'files');
//
////        $job = (new WriteLogFile($log))->onQueue('logs');
////        $this->dispatch($job);
//
//    }
}
