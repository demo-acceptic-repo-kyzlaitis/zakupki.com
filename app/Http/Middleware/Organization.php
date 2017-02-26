<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Session;

class Organization
{
    protected $except = [
        'notification/*'
    ];


    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        if (!$this->shouldPassThrough($request)) {
            if (!$request->user()->hasOrganization()) {

                Session::flash('flash_error', 'Спочатку необхідно заповнити дані організації.');
                return redirect()->route('organization.create');
            }
            if (empty($request->user()->organization->type)) {
                Session::flash('flash_error', 'Вкажіть роль вашої організації.');
                return redirect()->route('organization.edit');
            }

            if ($request->user()->organization->kind_id == 0 && $request->user()->organization->type == 'customer') {
                Session::flash('flash_modal', 'Будь ласка, вкажіть тип замовника');
                return redirect()->route('organization.edit');
            }
        }

        return $next($request);
    }

    /**
     * Determine if the request has a URI that should pass through Organization verification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function shouldPassThrough($request)
    {
        foreach ($this->except as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if ($request->is($except)) {
                return true;
            }
        }

        return false;
    }
}
