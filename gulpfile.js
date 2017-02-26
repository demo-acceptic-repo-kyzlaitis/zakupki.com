var elixir = require('laravel-elixir');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(function(mix) {
    mix.sass('app.scss');

    mix.scripts([
        'libs/bootstrap-notify.min.js',
        'public/notify-upload.js'
    ], './public/js/notification.js');

    mix.scripts([
        'public/complaint-cancellation.js',
        'public/guarantee.js',
        'public/ajax-tender-validation.js',
        'public/replaceCommaWithDot.js',
        'libs/bootstrap-session-timeout.js', // https://github.com/orangehill/bootstrap-session-timeout
        'public/sessionExpiration.js'
    ], './public/js/public.js');

    mix.scripts([
        'admin/email-notifications.js'
    ], './public/js/admin.js');

    mix.scripts([
        'libs/bootstrap-notify.min.js',//http://bootstrap-notify.remabledesigns.com/
        'libs/bootstrap-select.min.js'//https://silviomoreto.github.io/bootstrap-select/

    ], './public/js/libs.js');

    mix.styles([
        'custom.css',
        'bootstrap-select.min.css'//https://silviomoreto.github.io/bootstrap-select/
    ], './public/css/custom.css');
});
