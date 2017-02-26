<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\CreateOrganizationRequest;
use App\Model\Contacts;
use App\Model\PaymentHistory;
use App\Model\User;
use App\Model\UserBalance;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Input;
use Validator;

class PaysystemController extends Controller
{

    public function index()
    {

        return view('pages/organization/lists', compact('organizations'));
    }

    public function cashless() {
        $unconfirmeds = PaymentHistory::pending()->cashless()->orderBy('updated_at', 'desc')->paginate(25);
        return view('admin.pages.payment.list', compact('unconfirmeds'));
    }
    public function manualPay($id){
        $unconfirmeds = User::find($id)->balance;
        return view('admin.pages.payment.manual', compact('unconfirmeds'));
    }
    public function manualRefill(request $request){
        $user_id = $request->user_id;
        $sum = $request->amount;
        $ub = UserBalance::find($user_id);
        $ub->balance = $ub->balance+$sum;
        $ub->save();
        $ph = new PaymentHistory();
        $ph->amount = $sum;
        $ph->currency = PaymentHistory::UAH;
        $ph->who_add = Auth::id();
        $ph->payment_services = PaymentHistory::BILLING;
        $ph->status_our = 1;
        $ph->status_ps = PaymentHistory::COMPLETED;
        $ph->save();
        if ($ub->save() == true && $ph->save() == true ){
            Session::flash('flash_message', 'Баланс успешно пополнен');
            Event::fire(new RefillBalanceEvent($ub,$sum));
           // return back();
            return redirect()->route('admin::organization.index');
        }

    }

    public function cashlessHistory()
    {
        $unconfirmeds = PaymentHistory::cashless()->paginate(25);
        return view('admin.pages.payment.history', compact('unconfirmeds'));
    }
    public function payHistory($id){
        $unconfirmeds = PaymentHistory::where('user_id',$id)->paginate(25);
        $nom = $id;
        return view('admin.pages.payment.allhistory', compact('unconfirmeds','nom'));
    }

    public function cashlessSearch(Request $request){
        $data = $request->all();
        $search = $data['id'];
        $unconfirmeds = PaymentHistory::cashless()->paginate(25);
        return view('admin.pages.payment.history', compact('unconfirmeds'));
    }
    public function liqPayHistory(){
        $unconfirmeds = PaymentHistory::liqPay()->orderBy('updated_at', 'desc')->paginate(25);
        return view('admin.pages.payment.liqpay', compact('unconfirmeds'));
    }

    public function balance()
    {
        $userbalance = UserBalance::paginate(25);
        return view('admin.pages.payment.balance', compact('userbalance'));

    }

    public function balanceSearch()
    {
        if (isset($_GET['user_id']) && !empty($_GET['user_id'])) {
            $userbalance = UserBalance::where('user_id', '=', $_GET['user_id'])->paginate(25);
            return view('admin.pages.payment.balance', compact('userbalance'));
        } else {
            Session::flash('flash_message', 'Будь ласка запоніть поле для пошуку по ID');
            return redirect()->back();
        }
    }

    public function search(Request $request)
    {

        $data = $request->all();
        $id = $data['id'];
        if (!empty($id)) {
            $unconfirmeds = PaymentHistory::cashless()->completed()->where('id', '=', $id)->paginate(25);
            return view('admin.pages.payment.list', compact('unconfirmeds'));
        } else {
            Session::flash('flash_message', 'Будь ласка запоніть поле для пошуку по ID');
            return redirect()->back();
        }


    }

    public function confirm($id)
    {
        $ph = PaymentHistory::find($id);
        $ph->status_ps = 'completed';
        $ph->status_our = 1;
        $ph->who_add = Auth::id();
        $ph->save();
        $ub = UserBalance::find($ph->user_id);
        $ub->balance = $ub->balance + $ph->amount;
        $ub->activate = 1; //Активируем счет, после активации пользователь может выбирать платежную сиситему
        $ub->save();
        if ($ph->save() && $ub->save()) {
            Session::flash('flash_message', 'Підтвердження платежу було успішним.');
            $unconfirmeds = PaymentHistory::cashless()->pending()->paginate(25);
            return view('admin.pages.payment.list', compact('unconfirmeds'));
        }
    }

    public function repay()
    {
        $unconfirmeds = PaymentHistory::invoice()->where('user_cencel', '=', '1')->pending()->paginate(25);
        return view('admin.pages.payment.repay', compact('unconfirmeds'));
    }

    public function repaySearch(Request $request)
    {
        $data = $request->all();
        $id = $data['id'];
        if (!empty($id)) {
            $unconfirmeds = PaymentHistory::invoice()->pending()->where('user_cencel', '=', '1')->where('id', '=', $id)->paginate(25);
            return view('admin.pages.payment.repay', compact('unconfirmeds'));
        } else {
            Session::flash('flash_message', 'Будь ласка запоніть поле для пошуку по ID');
            return redirect()->back();
        }

    }

    public function moneyback($id)
    {
        $ph = PaymentHistory::find($id);
        $ph->status_ps = 'returned';
        $ph->status_our = 1;
        $ph->who_add = Auth::id();
        $ph->save();
        $ub = UserBalance::find($ph->user_id);
        $ub->balance = $ub->balance + $ph->amount;
        $ub->save();
        if ($ph->save() && $ub->save()) {
            Session::flash('flash_message', 'Повернення коштів було успішним.');
            $unconfirmeds = PaymentHistory::invoice()->pending()->where('user_cencel', '=', '1')->paginate(25);
            return view('admin.pages.payment.repay', compact('unconfirmeds'));
        }

    }

    public function uploadcsv(Request $request)
    {
        error_reporting(-1);
        header('Content-Type: text/html; charset=utf-8');

        $file = Input::file('csv');
        dd($file);
        if (empty($file)) {
            Session::flash('flash_message', 'Будь ласка виберіть файл в форматі csv.');
            return redirect()->back();
        }
        $a = array();
        $handle = fopen($file, "r");
        while (!feof($handle)) {
            $a[] = fgetcsv($handle, 0, ";");
        }

        $i = 0;
        $orders = array();
        foreach ($a as $val) {
            if (is_array($val)) {
                foreach ($val as $v) {
                    $enc = mb_detect_encoding($v);
                    if ($enc == 'UTF-8') {
                        $orders[$i][] = iconv('windows-1251', $enc, $v);
                    } else {
                        $orders[$i][] = iconv('ASCII', 'UTF-8//IGNORE', $v);
                    }
                }
            }
            $i++;
        }
        $results = array();
        $i = 0;
        foreach ($orders as $order) {
            foreach ($order as $ord) {
                if (preg_match_all('/(?<=-\s|(?<=-))\d{7}(?=\s+)/', $ord, $found) == true) {
                    $results[$i] = $found;
                    $i++;
                }
            }

        }

        dd($results);
        fclose($handle);


        //dd($file);
    }

}