<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Log;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {


        env('APP_ENV') == 'server' ? URL::forceSchema('https') : URL::forceSchema('http');
        \Validator::extend('float', function($attribute, $value, $parameters) {
            dd([is_numeric($value), $attribute, $value, $parameters]);
        });

        Blade::directive('hide', function() {
            return "style=\"display:none;\"";
        });

        Validator::extend('dk2015', function ($attribute, $value, $parameters, $validator) {
            return preg_match('/[0-9]{8}[-][0-9]{1}/', $value);
        });

        Validator::extend('dk2010', function ($attribute, $value, $parameters, $validator) {
            return preg_match('/[0-9]{2}[.][0-9]{2}[.][0-9]{1}/', $value);
        });

        Validator::extend('ukrainian_phone', function ($attribute, $value, $parameters, $validator) {
            return preg_match('/\+380[0-9]{9}/A', $value); //+380935017175 если переде номером будет пробел не свалидирует
        });
        
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        require_once __DIR__ . '/../Helpers/helpers.php';
    }
}
