<?php

namespace App\Http\Controllers\Admin;

use App\Events\UserRegisterEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Model\Role;
use App\Model\User;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Input;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;



class UserController extends Controller
{


    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        $users = User::orderBy('created_at', 'DESC')->paginate(20);

        return view('admin.pages.user.list', ['users' => $users]);
    }


    /**
     * Show the form for editing the specified resource.
     * @return Response
     * @internal param int $id
     *
     */
    public function edit($userId)
    {
        $user = User::find($userId);
        $roles = Role::get();
        $userRoles = [];
        foreach ($user->roles as $role)
            $userRoles[] = $role->id;

        return view('admin.pages.user.edit', compact('user', 'roles', 'userRoles'));
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
    public function update(UpdateUserRequest $request, $userId)
    {
        $user = User::find($userId);
        $data = $request->all();
        if ($data['password'] === '') {
            unset($data['password']);
        }
        $user->update($data);
        $user->roles()->sync(array_keys($data['roles']));

        \Session::flash('flash_message', 'Дані успішно оновлені');

        return redirect()->back();
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $remember = $request->input('remember') == '1' ? true : false;;
        if (Auth::attempt($request->only(['email', 'password']), $remember)) {
            return redirect()->intended();
        }

        return redirect()->route('user.login')->withInput()->withErrors(['wrongLogin'=>true]);
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
}
