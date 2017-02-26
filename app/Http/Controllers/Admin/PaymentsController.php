<?php

namespace App\Http\Controllers\Admin;

use App\Events\RefillBalanceEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Model\Order;
use App\Model\Organization;
use App\Model\PaymentService;
use App\Model\Product;
use App\Model\Transaction;
use App\Model\User;
use App\Model\UserBalance;
use App\Payments\Payments;
use Carbon\Carbon;
use Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class PaymentsController extends Controller
{
    protected $_mainManagerEmail = 'spasibova@zakupki.com.ua';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        $form = $request->get('form');

        if(empty($form)) {
            $form = [
                'organization_id' => '',
                'code'            => '',
                'email'           => '',
            ];
        }

        $query = Organization::where('source', '0');

        if($form['organization_id']) {
            $query->where('id', $form['organization_id']);
        }

        if(!empty($form['code'])) {
            $query->where('identifier', 'LIKE', "%{$form['code']}%");
        }

        $organizations   = $query->paginate(20);
        $payment_service = PaymentService::all();
        $ps              = [];

        foreach ($payment_service as $val) {
            $ps[$val['id']] = $val['name'];
        }

        krsort($ps);

        unset($ps[4]); // Убираем LiqPay
        return view('admin.pages.payments.list', compact('organizations', 'form','ps'));
    }

    public function edit($id)
    {
        if (Auth::user()->email == $this->_mainManagerEmail) {
            $transaction = Transaction::find($id);
            $paySystems = PaymentService::lists('name', 'id');

            return view('admin.pages.payments.edit', compact('transaction', 'paySystems'));
        }
    }

    public function update(Request $request, $id)
    {
        if (Auth::user()->email == $this->_mainManagerEmail) {
            try {
                DB::beginTransaction();
                $updateData = $request->all();

                $transaction = Transaction::find($id);
                $balance = Payments::balance($transaction->user_id);
                $balance->amount += -1 * $transaction->amount;  // Возврат баланса в предыдущее состояние
                $balance->amount += $request->get('amount');
                $balance->save();

                $transactions = Transaction::where('user_id', $transaction->user_id)->where('id', '>=', $transaction->id)->get();
                foreach ($transactions as $nexTransaction) {
                    //пересчитывет остаток по всем следующим транзакциям
                    $nexTransaction->balance += -1 * $transaction->amount;
                    $nexTransaction->balance += $request->get('amount');

                    $nexTransaction->save();
                }
                $transaction->update($updateData);


                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
            return redirect()->route('admin::payments.transactions');
        }
    }

    public function delete($id)
    {
        if (Auth::user()->email == $this->_mainManagerEmail) {
            try {
                DB::beginTransaction();

                $transaction = Transaction::find($id);
                $balance = Payments::balance($transaction->user_id);
                $balance->amount += -1 * $transaction->amount;  // Возврат баланса в предыдущее состояние
                $balance->save();

                $transactions = Transaction::where('user_id', $transaction->user_id)->where('id', '>', $transaction->id)->get();
                foreach ($transactions as $nexTransaction) {
                    //пересчитывет остаток по всем следующим транзакциям
                    $nexTransaction->balance += -1 * $transaction->amount;
                    $nexTransaction->save();
                }
                $transaction->delete();

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

            return redirect()->route('admin::payments.transactions');
        }
    }

    public function add(Request $request)
    {
        $user = User::find($request->get('user_id'));
        $payment_service = $request->get('payment_service');
        $balance = $user->balance;
        $sum = $request->get('amount');
        if (!$balance) {
            $balance = new UserBalance();
            $user->balance()->save($balance);
        }
        if($request->get('order')){
            $order = new Order([
                'amount' => $sum,
                'status' => 'new'
            ]);
            $user->orders()->save($order);
            $order->number = 'ЗА-' . str_pad($order->id, 7, '0', STR_PAD_LEFT);
            $order->save();
            $contacts['name'] = $user->organization->name;
            $contacts['phone'] = $user->organization->contact_phone;
            $org_id = $user->organization->id;
            return view('pages.payment.order', array('order_id' => $order->number, 'amount' => $order->amount, 'contacts' => $contacts, 'date' => date('d.m.Y', strtotime($order->updated_at)),
                'amountText'=> $this->num2str($order->amount), 'user_id'=> $org_id));
        }
        $balance->plus($request->get('amount'), $payment_service, ['comment' => $request->get('comment')],2);
        Event::fire(new RefillBalanceEvent($user,$sum));
        return redirect()->route('admin::payments.index');
    }

    public function removeCash(Request $request)
    {
        $user = User::find($request->get('user_id'));
        $balance = $user->balance;
        $sum = $request->get('amount-minus');
        if (!$balance) {
            $balance = new UserBalance();
            $user->balance()->save($balance);
        }
        $payment_service = $request->get('payment_service');
        $balance->minus($sum, 4, ['comment' => $request->get('comment')],$payment_service);
        //   Event::fire(new RefillBalanceEvent($user,$sum));
        return redirect()->route('admin::payments.index');
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

    public function morph($n, $f1, $f2, $f5) {
        $n = abs(intval($n)) % 100;
        if ($n>10 && $n<20) return $f5;
        $n = $n % 10;
        if ($n>1 && $n<5) return $f2;
        if ($n==1) return $f1;
        return $f5;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function transactions(Request $request)
    {
        $form = $request->get('form');

        $query = Transaction::orderBy('created_at', 'desc');
        if ($form['start_date']) {

            $query->where('created_at', '>', Carbon::parse($form['start_date']));
        }
        if ($form['use']) {
            $query->where('products_id', '=', $form['use']);
        }
        if ($form['ps'] && $form['ps'] != '') {
            $query->where('payment_service_id', '=', $form['ps']);
        }
        if ($form['end_date']) {
            $query->where('created_at', '<', Carbon::parse($form['end_date']));
        }

        if ($form['organization_id']) {
            $query->where('user_id', Organization::find($form['organization_id'])->user->id);
        }

        if ($form['direction'] > 0) {
            if ($form['direction'] == 1) {
                $query->where('amount', '>', 0);
            }
            if ($form['direction'] == 2) {
                $query->where('amount', '<', 0);
            }
        }

        $payments = $query->paginate(20);
        $productsFromDb = Product::all()->toArray();
        $products = [0=>'Все'];
        foreach($productsFromDb as $product){
            $products[$product['id']] = $product['name'];
        }
        $positiveQuery = clone $query;
        $negativeQuery = clone $query;

        $addSum = $positiveQuery->where('amount', '>', 0)->sum('amount');
        $minusSum = $negativeQuery->where('amount', '<', 0)->sum('amount');

        $payment_service = PaymentService::all();
        $ps = array();
        $ps[0] = '';
        foreach($payment_service as $val){
            $ps[$val['id']] = $val['name'];
        }
        $allowEdit = Auth::user()->email == $this->_mainManagerEmail;


        return view('admin.pages.payments.transactions', compact('payments', 'form', 'addSum', 'minusSum','products','ps', 'allowEdit'));
    }

    public function orders()
    {
        $orders = Order::paginate(20);

        return view('admin.pages.payments.orders', compact('orders'));
    }

    public function pay(Request $request)
    {
        $orderId = $request->get('order_id');
        $order = Order::find($orderId);
        Payments::balance($order->user_id)->plus($order->amount);
        $order->status = 'payed';
        $order->save();

        return redirect()->back();

    }

    public function clientbank()
    {
        return view('admin.pages.payments.clientbank');
    }
    
    public function upload(Request $request)
    {
        if ($request->hasFile('payments')) {
            $paymentsCsv = $request->file('payments')->openFile('r');
            $data = [];
            $paymentsCsv->fgetcsv(';'); //пропускаем заголовки

            while (!$paymentsCsv->eof()) {
                $line = $paymentsCsv->fgetcsv(';');
                if (isset($line[0])) {
                    preg_match('/ЗА-\d+/', iconv('Windows-1251', 'utf-8', $line[15]), $orderNumber);
                    $order = null;
                    if (isset($orderNumber[0]) && !empty($orderNumber[0])) {
                        $order = Order::where('number', $orderNumber[0])->first();
                    }
                    $dateTransaction = Carbon::parse($line[4]);
                    $line['date_transaction'] = $dateTransaction;
                    $line['organization'] = Organization::getByIdentifier($line[9], 0);
                    if ($line['organization']) {
                        $transaction = Transaction
                            ::where('date_transaction', $dateTransaction->toDateTimeString())
                            ->where('amount', $line[14] * 100)
                            ->where('user_id', $line['organization']->user->id)
                            ->first();
                    } else {
                        $transaction = false;
                    }
                    $line['transaction'] = $transaction;
                    $line['order'] = $order;
                    $data[] = $line;
                }
            }
            
            return view('admin.pages.payments.csv', compact('data'));
        }
    }

    public function commit(Request $request)
    {
        $data = $request->all();
        if (isset($data['line'])) {
            foreach ($data['line'] as $line) {
                $balance = Organization::find($line['organization_id'])->user->balance;
                $balance->plus($line['amount'], 1, ['comment' => $line['comment'], 'date_transaction' => $line['date']], 2);
                if (isset($line['order_id'])) {
                    $order = Order::find($line['order_id']);
                    $order->status = 'payed';
                    $order->save();
                }
            }
        }

        return redirect()->route('admin::payments.transactions');
    }
}
