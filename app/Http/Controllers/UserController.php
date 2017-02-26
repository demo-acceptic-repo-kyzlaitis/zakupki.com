<?php

namespace App\Http\Controllers;

use App\Events\UserRegisterEvent;
use App\Helpers\Mailable;
use App\Http\Requests;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Model\Organization;
use App\Model\ReloginHistory;
use App\Model\User;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;


class UserController extends Controller {



    use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    protected $loginPath = '/login';


    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        $users = User::all();

        return view('pages.user.lists', ['users' => $users]);
    }


    public function showOffer() {

        return view('pages.user.offer');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateUserRequest $request
     *
     * @return Response
     */
    public function create(CreateUserRequest $request)
    {
        $data = $request->all();
        $data['password'] = bcrypt($data['password']);
        $data['activation_code'] = md5(bcrypt($data['email']));
        $user = User::create($data);
        $user->save();
        Event::fire(new UserRegisterEvent($user));

//        if (Auth::attempt($request->only(['email', 'password']), true)) {
//            return redirect()->route('home');
//        }

        return view('pages.user.finish', ['user' => $user]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id) {

    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     * @internal param int $id
     *
     */
    public function edit() {

        if (Auth::check()) {
            $user = Auth::user();

            return view('pages.user.edit-form', ['user' => $user]);

        }

        return redirect('/');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateUserRequest|Request $request
     *
     * @return Response
     * @internal param int $id
     *
     */
    public function update(UpdateUserRequest $request) {

        if (Auth::check()) {
            Auth::user()->update($request->except('email'));

            \Session::flash('flash_message', 'Дані успішно оновлені');

            return redirect()->action('UserController@edit');
        }

        return redirect('/');
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $remember = $request->input('remember') == '1' ? true : false;
        if (Auth::attempt($request->only(['email', 'password']), $remember)) {

            return redirect()->intended();
        }

        return redirect()->route('user.login')->withInput()->withErrors(['wrongLogin'=>true]);
    }

    /**
     * Отправка письма для подтверждения email
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resend(Request $request)
    {
        $user = User::find($request->get('id'));
        if ($user && $user->active == 0) {
            Event::fire(new UserRegisterEvent($user));
        }

        \Session::flash('flash_message', 'Лист відправлено повторно');

        return view('pages.user.activate', ['user' => $user]);
    }

    public function activate($code)
    {
        $user = User::where('activation_code', $code)->first();
        if ($user) {
            $user->active = 1;
            $user->save();
            Auth::login($user);

            \Session::flash('flash_message', 'E-mail підтверджений.');
            \Session::flash('flash_modal', '<h2>Шановні користувачі !</h2><p>Ви перебуваєте на електронному майданчику "Zakupki UA" – авторизованому електронному майданчику за всіма рівнями акредитації відповідно до Закону України "Про публічні закупівлі", який забезпечує проведення публічних закупівель із використанням системи PROZORRO.</p>');
            return redirect()->route('organization.create');
        }

        \Session::flash('flash_error', 'Невірний код активації. Надішліть листа ще раз.');

        return redirect()->route('user.activate');
    }

    /**
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function logout()
    {
        if (Auth::check()) {
            Auth::logout();

            return redirect('/');
        }

        return redirect()->back();
    }

    /**
     * @return \Illuminate\View\View
     */
    public function home(){
        return view('pages.user.home-page');
    }

    /**
     * @return \Illuminate\View\View|string
     */
    public function register(){
        if (Auth::guest())
            return view("pages.user.registration-form");

        return redirect()->route('home');
    }

    public function relogin($id)
    {

        $user = Organization::find($id)->user;
        if (Auth::user()->super_user && $user) {
            $nomUser = Session::get('nominal_user_id');
                if ($nomUser == null)
                {
                    Session::put('nominal_user_id', Auth::user()->id);
                    $nomUser = Session::get('nominal_user_id');
                }
                Auth::loginUsingId($user->id);
            ReloginHistory::setEvent($nomUser,$id, 'Relogin' , 'User');
        }
        return redirect()->route('home');
    }

    public function keepAlive(Request $request) {

    }
}
