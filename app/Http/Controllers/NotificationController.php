<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Model\Notification;
use App\Model\User;
use App\Services\NotificationService\Model\NotificationTemplate;
use App\Services\NotificationService\NotificationService;
use App\Services\NotificationService\Tags;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    const DEFAULT_LANGUAGE = 'ua';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $notifications = Notification::personal()->orderBy('created_at', 'desc')->paginate(20);

        return view('pages.notification.list', ['notifications' => $notifications]);
    }

    public static function viewNewMsg(){
        if (Auth::user()) {
            $notification = Notification::personal()->notReaded()->orderBy('created_at', 'asc')->first();
           return $notification;
        }

    }

    public function readble(Request $request)
{

    $id = $request->get('id');
    $notifications = Notification::find($id);
    $notifications->readed_at = date('Y-m-d H:i:s');
    $notifications->save();
}

    public function unsubscribe()
    {
        $user = Auth::user();

        $user->subscribe = false;
        $result = $user->save();
        if ($result == true){
            Session::flash('flash_message', 'Ви успішно відписані від розсилки.');

            return view('pages.notification.unsubscribe');
        }

    }

    public function subscribe()
    {
        $user = Auth::user();
        $user->subscribe = true;
        $result = $user->save();
        if ($result == true){
            Session::flash('flash_message', 'Ви успішно підписані на розсилку.');

            return view('pages.notification.unsubscribe');
        }

    }

    public function bidConfirm(Request $request){
        $notification_service = new NotificationService();
        $tags = new Tags();
        $notification_service->create($tags, NotificationTemplate::OFFER_ERROR_LOWER, Auth::user()->id, self::DEFAULT_LANGUAGE);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $notification = Notification::where('user_id', Auth::user()->id)->where('id', $id)->first();
        if (!$notification) {
            abort(403);
        }
        $notification->readed_at = date('Y-m-d H:i:s');
        $notification->save();

        return view('pages.notification.detail', ['notification' => $notification]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
