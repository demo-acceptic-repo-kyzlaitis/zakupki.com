<?php

namespace App\Http\Controllers\Admin;

use App\Events\TargetNotificationEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Model\Organization;
use App\Model\Status;
use Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Validator;


class NotificationController extends Controller
{
    public function index(Request $request){

        $tenderStatus = Status::where('namespace', 'tender')->lists('description', 'status');
        $bidStatus = Status::where('namespace', 'bid')->lists('description', 'status');
        $tenderStatus->prepend(null, '');
        $bidStatus->prepend(null, '');
        return view('admin.pages.notification.index', compact('tenderStatus', 'bidStatus'));
    }

    public function store(Request $request) {
        $data = $request->all();

        if(empty($data['organizationType']) &&
            empty($data['tenderStatus']) &&
            empty($data['bidStatus']) &&
            empty($data['title'] &&
            empty($data['mode']) &&
            empty($data['text'])
            )) {

            Session::flash('flash_message', 'Введіть хоча б один критерій пошуку');

            return redirect()->route('admin::notification.index');
        }

        $query = Organization::where('source', 0)->where('confirmed', 1);
        // или кастомер или супплаер
        if(!empty($data['organizationType'])) {
            $query->where('type', 'like', $data['organizationType']);
        }

        //режими реальнный или тестовый
        if(!empty($data['mode'])) {
            $query->where('mode', $data['mode']);
        }

        // статусы тенедеров
        if(!empty($data['tenderStatus'])) {
            $query->whereHas('tenders', function($query) use($data) {
                $query->where('status', 'like', $data['tenderStatus']);
            });
        }

        // статусы бидов
        if(!empty($data['bidStatus'])) {
            $query->whereHas('bids', function($query) use ($data) {
                $query->where('status', 'like', $data['bidStatus']);
            });
        }

        Event::fire(new TargetNotificationEvent($query, $data));

        Session::flash('flash_message', 'Повідомлення були створенні та найближчим часом будуть надісланні');

        return redirect()->route('admin::notification.index');
    }
}
