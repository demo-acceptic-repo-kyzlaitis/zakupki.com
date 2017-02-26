<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Jobs\SyncBid;
use App\Model\Bid;
use App\Model\Organization;
use App\Model\User;
use Illuminate\Support\Facades\Mail;
use App\Model\Notification;
use App\Model\Classifiers;
use App\Model\Codes;
use App\Model\Currencies;
use App\Model\Tender;
use App\Model\TendersRegions;
use App\Model\Award;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function index(Request $request){
//        $bids = Bid::where('participation_url','')->whereHas('tender',function($q){
//            $q->where('status','active.auction');
//        })->get();
//        if ($bids->count() > 0) {
//            foreach ($bids as $bid) {
//                Mail::queue('emails.bid-links', ['bid' => $bid], function ($message) {
//                    $message->to('spasibova@zakupki.com.ua', $name = null)->subject('Є активні пропозиції без посилання на аукціон');
//                    $message->to('manager@zakupki.com.ua', $name = null)->subject('Є активні пропозиції без посилання на аукціон');
//                    $message->to('dexvsmax@gmail.com', $name = null)->subject('Є активні пропозиції без посилання на аукціон');
//                    $message->to('vitaliyminenko@mail.ru', $name = null)->subject('Є активні пропозиції без посилання на аукціон');
//                });
//            }
//            dd($bids);
//        }


//        $user = User::findOrFail(10);
//
//        Mail::send('emails.activate', ['user' => $user], function ($m) use ($user) {
//            $m->from('no-reply@etp.zakupki.com.ua', 'zakupki.com.ua');
//
//            $m->to($user->email, $user->name)->subject('Регистрация');
//        });


//        $bids = [32623, 32624, 32625, 32626, 32627, 32628];
//        $bids = [32623];
//        $data = [];
//
//        foreach ($bids as $bid_id) {
//            $bid = Bid::find($bid_id);
//            $this->dispatch((new SyncBid($bid->id))->onQueue('bids'));
//
//            $tender = Tender::find($bid->tender_id);
//            $api = new \App\Api\Api();
//            $data[] = $api->get($tender->cbd_id, '/bids/' . $bid->cbd_id, $bid->access_token);
//        }
//
//        dd($data);


//        $bids = Bid::where('access_token', '!=', '')
//            ->orWhere('participation_url', '')
//            ->get(['id']);
//        dd($bids);
//
        $org = \App\Model\Organization::getByIdentifier($request->get('id'));
        dd($org);
    }
    public function getAdminTax($sum){
        $current_year = date('Y');
        if($current_year == '2016'){
            if($sum < 20000){
                return 7;
            }elseif($sum < 50000){
                return 50;
            }elseif($sum < 200000){
                return 150;
            }elseif($sum < 1000000){
                return 250;
            }elseif($sum > 1000000){
                return 700;
            }
        }else{
            if($sum < 20000){
                return 5;
            }elseif($sum < 50000){
                return 25;
            }elseif($sum < 200000){
                return 80;
            }elseif($sum < 1000000){
                return 110;
            }elseif($sum > 1000000){
                return 400;
            }
        }

    }

}