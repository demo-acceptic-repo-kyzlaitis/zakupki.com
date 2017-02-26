<?php

namespace App\Http\Middleware;

use App\Model\User;
use Closure;
use Auth;


class AuthenticateOnceWithBasicAuth
{
    /**
     *
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next) {

        if(Auth::onceBasic('email') == null) {

            $user = User::where('email', 'like', $request->getUser())->first();

            if ($user == null) {
                return response('user was not found', 404);
            }

            Auth::loginUsingId($user->id);

            return $next($request);
        }

        return response('', 401);
    }
}
