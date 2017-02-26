<?php

namespace App\Http\Controllers;

use App\Model\Bill;
use App\Payments\Payments;
use DB;
use App\Quotation;
use App\Model\PaymentHistory;
use App\Model\User;
use App\Model\UserBalance;
use App\Model\PaymentService;
use App\Model\Order;
use App\Model\Log;
use App\Http\Requests;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Auth;
use Input;
use Validator;

class PaymentController extends Controller
{
    public function index(Request $request) 
    {
        $user = Auth::user();
        $order = new Order([
            'amount' => $request->input('amount'),
            'status' => 'new'
        ]);
        $user->orders()->save($order);
        $order->number = 'ЗА-' . str_pad($order->id, 7, '0', STR_PAD_LEFT);
        $order->save();

        Session::flash('flash_message', 'Рахунок створений.');
        return redirect()->route('Payment.pay');

    }
    public function index2(Request $request)
    {
        $contacts['name'] = Auth::user()->organization->contact_name;
        $contacts['phone'] = Auth::user()->organization->contact_phone;
        $request->all();
        $service_id = $request['service_id'];
        if ($service_id == null){
            $service_id = 2;
        }
        $amount = $request['amount'];
        $arAmm = explode('.',$amount);
        if (isset($arAmm[1])){
          if(isset($arAmm[1][1])){
              $amount = $amount;
          }else{
              $amount = $amount.'0';
          }
        }else{
            $amount = $arAmm[0].'.00';
        }
        $amountText = $this->num2str($amount);

        //$entity_type = $request['entity_type'];
        //$entity_id = $request['entity_id'];
        $user_id = Auth::id();
        $userBalance = UserBalance::where('user_id', $user_id)->first();

        if (!$userBalance->activate || $service_id == 2) {

            $status_our = 0;
            if (!$userBalance->activate) {
                $ph = PaymentHistory::where('user_id', '=', $user_id)->cashless()->pending()->first();
                if (count($ph) == 0) {
                    $ph = new PaymentHistory;
                    $ph->payment_services = $service_id;
                    $ph->amount = $amount;
                    $ph->status_our = $status_our;
                    $ph->user_id = $user_id;
                    $ph->status_ps = PaymentHistory::PENDING;
                    $ph->save();
                    $order_id = $ph->id;
                } else {
                    $ph->amount = $amount;
                    $ph->status_ps = PaymentHistory::PENDING;
                    $ph->save();
                    $order_id = $ph->id;
                }
            } else {
                $ph = new PaymentHistory;
                $ph->payment_services = $service_id;
                $ph->amount = $amount;
                $ph->user_id = $user_id;
                $ph->status_our = $status_our;
                $ph->status_ps = PaymentHistory::PENDING;
                $ph->save();
                $order_id = $ph->id;
            }
            $date = date('d.m.Y', strtotime($ph->updated_at->toDateTimeString()));
            $countZeros = 7 - mb_strlen($order_id);
            $zeros = '';
            for ($i = 1; $i <= $countZeros; $i++) {
                $zeros = $zeros . '0';
            }
            $order = 'ЗА-' . str_pad($order_id, 7, '0', STR_PAD_LEFT);

            return view('pages.payment.order', array('order_id' => $order, 'amount' => $amount, 'contacts' => $contacts, 'date' => $date,'amountText'=>$amountText,'user_id'=>$user_id));
        } elseif ($service_id == 1) {
            return view('pages.payment.liqpay', $this->liqPay($amount));
        }
    }

    public function printOrder(Request $request)
    {
        $order = Auth::user()->orders()->find($request->get('id'));
        $contacts['name'] = Auth::user()->organization->name;
        $contacts['phone'] = Auth::user()->organization->contact_phone;

        if (!$order) {
            abort(403);
        }
        $org_id = Auth::user()->organization->id;

        return view('pages.payment.order', array('order_id' => $order->number, 'amount' => $order->amount, 'contacts' => $contacts, 'date' => date('d.m.Y', strtotime($order->updated_at)),
            'amountText'=> $this->num2str($order->amount), 'user_id'=> $org_id));
    }

    public function saveAsPdf(Request $request) {

        $order = Auth::user()->orders()->find($request->get('id'));
        if (!$order) {
            abort(403);
        }
        $contacts['name'] = Auth::user()->organization->name;
        $contacts['phone'] = Auth::user()->organization->contact_phone;
        $org_id = Auth::user()->organization->id;

        $amountText = $this->num2str($order->amount);

        $pdf = \PDF::loadView('pages.payment.order-pdf', array('order_id' => $order->number, 'amount' => $order->amount, 'contacts' => $contacts, 'date' => date('d.m.Y', strtotime($order->updated_at)),
                                                               'amountText'=> $this->num2str($order->amount), 'user_id'=> $org_id));


        return $pdf->download('invoice.pdf');
    }

    public function liqPay($amount)
    {
        $server_url = 'http://'.$_SERVER["HTTP_HOST"].'/paysystems/callback';
        $result_url = 'http://'.$_SERVER["HTTP_HOST"];
        $public_key = 'i50027726300';
        $private_key = 'ANwnEPbkL3tUCBUt3kK0gTf7l5OA7CIkE5RHw42e';
        $service_id = PaymentHistory::LIQPAY;// LiqPay
        $user_id = $id = Auth::id();
        $status_our = 0; //Платеж создан
        $order_id = $this->get_order($user_id, $service_id, $amount, $status_our);

        $json_string = json_encode(array(
            'version' => '3',
            'public_key' => $public_key,
            'amount' => $amount,
            'version' => '3',
            'currency' => PaymentHistory::UAH,
            'description' => 'Paid services',
            'action' => 'pay',
            'sandbox'=>'1',
            'order_id' => $order_id,
            'server_url' => $server_url,
            'result_url' => $result_url,
            'language' => 'ru'));
        $data = base64_encode($json_string);
        $signature = base64_encode(sha1($private_key . $data . $private_key, 1));
        $ph = PaymentHistory::find($order_id);
        $ph->save();
        $info = array('data' => $data, 'signature' => $signature);
        return $info;

    }

    public function callback()
    {
        if (isset($_POST['data'])) {

            $data = json_decode(base64_decode($_POST['data']), 1);
            $logTxt = base64_decode($_POST['data']);
            $ph_id = (int)$data['order_id'];
            $ph = PaymentHistory::find($ph_id);
            $user_id = $ph->user_id;
            if (isset($ph->status_ps)) {
                $status_ps = $ph->status_ps;
            } else {
                $status_ps = 0;
            }
            if ($data['status'] == 'success' && $status_ps != $data['status']) {
                DB::beginTransaction();
                $this->log($logTxt. date('Y-m-d H:s'));
                $ph->transaction_id = $data['payment_id'];
                $ph->status_ps = $data['status'];
                $ph->status_our = 1; //Operation Done
                $ph->currency = PaymentHistory::UAH;
                $ph->amount = $data['amount'];
                $ph->sender_commission = $data['sender_commission'];
                $ph->receiver_commission = $data['receiver_commission'];
                $ph->save();
                $userBalance = UserBalance::find($user_id);
                if ($userBalance == NULL) {
                    $ub = new UserBalance;
                    $ub->user_id = $user_id;
                    $ub->balance = (float)$bank_amount = round($data['amount'], 2, PHP_ROUND_HALF_DOWN);
                    $ub->currency = $data['currency'];
                    $ub->save();
                } else {
                    $current_balance = round($userBalance->balance, 2, PHP_ROUND_HALF_DOWN);
                    $bank_amount = round($data['amount'], 2, PHP_ROUND_HALF_DOWN);
                    $new_balance = $current_balance + $bank_amount;
                    $userBalance->balance = $new_balance;
                    $userBalance->currency = $data['currency'];
                    $userBalance->save();
                }
                DB::commit();
            } else {
                $this->log($logTxt. date('Y-m-d H:s'));
                $ph->transaction_id = $data['payment_id'];
                $ph->status_ps = $data['status'];
                $ph->save();
            }
        } else {
            $this->log('API Error. Not found response from paysystem' . date('Y-m-d H:s'));
        }
        return 'ok';
    }

    protected function get_order($user_id, $service_id, $amount, $status_our)
    {
        $ph = new PaymentHistory();
        $ph->user_id = $user_id;
        $ph->payment_services = $service_id;
        $ph->amount = $amount;
        $ph->currency = PaymentHistory::UAH;
        $ph->status_our = $status_our;
        $ph->status_ps = PaymentHistory::PENDING;
        $ph->save();
        return $ph->id;
    }

    public function pay()
    {
        $userBalance = Payments::balance();

        $user =  Auth::user()->paymentHistory;
        $balance = $userBalance->amount;
        $services = ['2' => 'Безготівковий'];
        $orders = Auth::user()->orders()->orderBy('created_at', 'desc')->paginate(20);

        $transactions = Auth::user()->transactions()->orderBy('created_at', 'desc')->paginate(20);


        return view('pages.payment.pay', compact('user', 'balance', 'services', 'orders', 'transactions'));
    }

    public static function withdrawal($userBalnace, $withdrawalSum, $entityName, $entityId, $payment_services, $tender_id)
    {
        if ($withdrawalSum > $userBalnace) {
            Session::flash('flash_message', 'На Вашому рахунку недостатньо коштів. Для того, щоб продовжити, будь ласка, поповніть свій баланс.');
            return false;
        } elseif ($withdrawalSum <= $userBalnace) {
            DB::beginTransaction();
            $userBalance = UserBalance::find(Auth::id());
            $userBalance->balance = $userBalnace - $withdrawalSum;
            $userBalance->save();
            $ph = new PaymentHistory();
            $ph->amount = $withdrawalSum;
            $ph->currency = PaymentHistory::UAH;
            $ph->entity_id = $entityId;
            $ph->entity_type = $entityName;
            $ph->move = 0; //Поступление к нам
            $ph->status_our = 1; //Успешно пополнены
            $ph->status_ps = PaymentHistory::PENDING; // Означает что поплнеие находится в режиме заморозки счета до окончания аукциона в случае если используется payment_services = 3
            $ph->user_id = Auth::id();
            $ph->tender_id = $tender_id;
            $ph->payment_services = $payment_services; //  payment_services = 3 Означает внутренний платеж в нашей системе + выставляем свойство pending  в status_ps
            $ph->save();
            DB::commit();

            if ($userBalance->save() && $ph->save()) {
                Session::flash('flash_message', 'Плата пройшла успишно Ваш баланс складае' . $userBalance->balance . ' UAH');
            }

            return true;
        }
    }

    public function log($txt)
    {
        $log = new Log();
        $log->stings = $txt;
        $log->exist = 'Yes';
        $log->save();
    }


    public function loadview()
    {
        return view('pages.pay.upload');
    }
    public function num2str($num) {
        $nul='ноль';
        $ten=array(
            array('','одна','дві','три','чотири','п`ять','шість','сім', 'вісім','дев`ять'),
            array('','одна','дві','три','чотири','п`ять','шість','сім', 'вісім','дев`ять'),
        );
        $a20=array('десять','одинадцять','дванадцять','тринадцять','чотирнадцять' ,'п`ятнадцять','шістнадцять','сімнадцять','вісімнадцять','дев`ятнадцять');
        $tens=array(2=>'двадцать','тридцать','сорок','п`ятьдесят','шістдесят','сімдесят' ,'вісімдесят','дев`яносто');
        $hundred=array('','сто','двісті','триста','чотириста','п`ятсот','шістсот', 'сімсот','вісімсот','дев`ятсот');
        $unit=array( // Units
            array('копійка' ,'копійки' ,'копійок',	 1),
            array('гривня'   ,'гривні'   ,'гривень'    ,0),
            array('тисяча'  ,'тисячі'  ,'тисяч'     ,1),
            array('мільйон' ,'мільйона','мільйонів' ,0),
            array('мільярд','мільярда','мільярдів',0),
        );
        //
        list($rub,$kop) = explode('.',sprintf("%015.2f", floatval($num)));
        $out = array();
        if (intval($rub)>0) {
            foreach(str_split($rub,3) as $uk=>$v) { // by 3 symbols
                if (!intval($v)) continue;
                $uk = sizeof($unit)-$uk-1; // unit key
                $gender = $unit[$uk][3];
                list($i1,$i2,$i3) = array_map('intval',str_split($v,1));
                // mega-logic
                $out[] = $hundred[$i1]; # 1xx-9xx
                if ($i2>1) $out[]= $tens[$i2].' '.$ten[$gender][$i3]; # 20-99
                else $out[]= $i2>0 ? $a20[$i3] : $ten[$gender][$i3]; # 10-19 | 1-9
                // units without rub & kop
                if ($uk>1) $out[]= $this->morph($v,$unit[$uk][0],$unit[$uk][1],$unit[$uk][2]);
            } //foreach
        }
        else $out[] = $nul;
        $kopTens = array('0'=>'нуль',
            '1'=>'десять',
            '2'=>'двадцать',
            '3'=>'тридцать',
            '4'=>'сорок',
            '5'=>'п`ятьдесят',
            '6'=>'шістдесят',
            '7'=>'сімдесят',
            '8'=>'вісімдесят',
            '9'=>'дев`яносто',
            '10'=>'десять',
            '11'=>'одинадцать',
            '12'=>'двонадцать',
            '13'=>'тринадцать',
            '14'=>'чотирнадцать',
            '15'=>'п`ятнадцать',
            '16'=>'шістнадцать',
            '17'=>'сімнадцать',
            '18'=>'вісімнадцать',
            '19'=>'дев`ятнадцать',
            );
        $kopOnce = array(
            '1'=>'одна',
            '2'=>'дві',
            '3'=>'три',
            '4'=>'чотири',
            '5'=>'п`ять',
            '6'=>'шість',
            '7'=>'сім',
            '8'=>'вісім',
            '9'=>'дев`ять',);
//        if($kop[0] >= 2 && $kop[1] == 0 ){
//            $kops = $kopTens[$kop[0]];
//        }elseif($kop[0] > 1 && $kop[1] > 0 ){
//            $kops = $kopTens[$kop[0]].' '.$kopOnce[$kop[1]];
//        }elseif($kop[0] == 0 && $kop[1] > 0 ){
//            $kops = $kopOnce[$kop[1]];
//        }
//        elseif($kop[0] == 0 && $kop[1] == 0 ){
//            $kops = $kopTens[$kop[1]];
//        }
//        elseif($kop[0] == 1 && $kop[1] >= 0 ){
//            $kops = $kopTens[$kop];
//        }
        $out[] = $this->morph(intval($rub), $unit[1][0],$unit[1][1],$unit[1][2]); // rub
        $out[] = $kop.' '.$this->morph($kop,$unit[0][0],$unit[0][1],$unit[0][2]); // kop
        return trim(preg_replace('/ {2,}/', ' ', join(' ',$out)));
    }

    /**
     * Склоняем словоформу
     * @ author runcore
     */
    public function morph($n, $f1, $f2, $f5) {
        $n = abs(intval($n)) % 100;
        if ($n>10 && $n<20) return $f5;
        $n = $n % 10;
        if ($n>1 && $n<5) return $f2;
        if ($n==1) return $f1;
        return $f5;
    }

}
