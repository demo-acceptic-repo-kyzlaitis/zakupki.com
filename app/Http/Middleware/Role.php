<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Role
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $rolesParam)
    {
        $roles = explode('|', $rolesParam);

        if(!in_array(Auth::user()->organization->type, $roles)) {
            abort(403);
        }

        return $next($request);
    }
}
