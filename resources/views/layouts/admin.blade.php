<!DOCTYPE html>
<html lang="en">
@include('admin.partials.common.header')
<style>
    /*
 * Base structure
 */

    /* Move down content because we have a fixed navbar that is 50px tall */
    body {
        padding-top: 50px;
    }


    /*
     * Global add-ons
     */

    .sub-header {
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }

    /*
     * Top navigation
     * Hide default border to remove 1px line.
     */
    .navbar-fixed-top {
        border: 0;
    }

    /*
     * Sidebar
     */

    /* Hide for mobile, show later */
    .sidebar {
        display: none;
    }
    @media (min-width: 768px) {
        .sidebar {
            position: fixed;
            top: 51px;
            bottom: 0;
            left: 0;
            z-index: 1000;
            display: block;
            padding: 20px;
            overflow-x: hidden;
            overflow-y: auto; /* Scrollable contents if viewport is shorter than content. */
            background-color: #f5f5f5;
            border-right: 1px solid #eee;
        }
    }

    /* Sidebar navigation */
    .nav-sidebar > li > a {
        padding-right: 20px;
        padding-left: 20px;
    }
    .nav-sidebar > .active > a,
    .nav-sidebar > .active > a:hover,
    .nav-sidebar > .active > a:focus {
        background-color: #f5f5f5;
    }


    /*
     * Main content
     */

    .main {
        padding: 20px;
    }
    @media (min-width: 768px) {
        .main {
            padding-right: 40px;
            padding-left: 40px;
        }
    }
    .main .page-header {
        margin-top: 0;
    }


    /*
     * Placeholder dashboard ideas
     */

    .placeholders {
        margin-bottom: 30px;
        text-align: center;
    }
    .placeholders h4 {
        margin-bottom: 0;
    }
    .placeholder {
        margin-bottom: 20px;
    }
    .placeholder img {
        display: inline-block;
        border-radius: 50%;
    }
</style>
<body>


<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#">Zakupki.com.ua</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
            <ul class="nav navbar-nav navbar-right">
                <li><a href="{{route('user.logout')}}">Выход</a></li>
            </ul>
        </div>
    </div>
</nav>

<?php

    $menu = [
            'Пользователи'         => [
                    'Пользователи' => ['user', 'index'],
                    'Организации'  => ['organization', 'index'],
                    'История Релогирования'  => ['relogin', 'index'],
            ],
            'Тендеры'              => [
                    'Тендeры' => ['tender', 'index'],
            ],
            'Предложения'              => [
                    'Предложения' => ['bids', 'index'],
            ],
            'Платежи' => [
                    'Баланс' => ['payments','index'],
                    'Транзакции' => ['payments', 'transactions'],
                    'Счета' => ['payments', 'orders'],
                    'Клиент-банк' => ['payments', 'clientbank']
            ],
//            'Платежи' => [
//                    'Безналичный пополнение' => ['paysystem','cashless'],
//                    'История безналичный пополнений' => ['paysystem','cashlessHistory'],
//                    'Балнс пользователей' => ['paysystem','balance'],
//                    'Возврат средств' => ['paysystem','repay'],
//            ],
            'Имейлы и уведомления' => [
                    'Emails'       => ['email', 'index'],
                    'Notification' => ['notification', 'index'],
            ],
            'Контроль версии' => [
                'Смена версии' => ['git', 'list'],
            ],
            'Пошукові агенти' => [
                'Анкети користувачів на модорацію' => ['agent', 'index'],
                'Пошукові агенти активовані' => ['agent', 'activeList'],
            ],
            'Выгрузка файлов' => [
                    'Жалобы' => ['complaint', 'index']
            ],
            'База данных' => [
                    'Очередь' => ['db', 'showQueue']
            ],
    ];
    if (Auth()->check() && Auth()->user()->has_role('business')) {
        $menu['Статистика'] = [
            'Организации' => ['stats', 'organizations']
        ];
    }
    $prefix =  str_replace('admin/', '', Route::getCurrentRoute()->getPrefix());
    $route =  Route::getCurrentRoute()->getName();

?>

<div class="container-fluid wrapper">
    <div class="row">
        <div class="col-sm-3 col-md-2 sidebar">
            <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
                @foreach($menu as $title => $items)
                    <div class="panel panel-primary">
                        <div class="panel-heading" role="tab" id="heading{{md5($title)}}">
                            <h4 class="panel-title">
                                <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse{{md5($title)}}" aria-expanded="true" aria-controls="collapse{{md5($title)}}">
                                    {{$title}}
                                </a>
                            </h4>
                        </div>

                        <?php
                            $active = 0;
                            foreach ($items as $item) {
                                if ($prefix == $item[0]) {
                                    $active = 1;
                                }
                            }
                        ?>
                        <div id="collapse{{md5($title)}}" class="panel-collapse collapse @if ($active == 1) in @endif" role="tabpanel" aria-labelledby="heading{{md5($title)}}">
                            <div class="panel-body">
                                <ul class="nav nav-sidebar">
                                    @foreach($items as $name => $routeName)
                                        <li @if (strpos($route, 'admin::'.$routeName[0].'.'.$routeName[1]) === 0) class="active" @endif><a href="{{route('admin::'.$routeName[0].'.'.$routeName[1])}}">{{$name}}</a></li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endforeach

            </div>
        </div>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
            <div>

                @if (Session::has('flash_message'))
                    <div class="container"><div class="alert alert-success" role="alert">
                            {{ Session::get('flash_message') }}
                        </div></div>
                @endif
                @if (Session::has('flash_error'))
                    <div class="container"><div class="alert alert-danger" role="alert">
                            {{ Session::get('flash_error') }}
                        </div></div>
                @endif
            </div>
            @yield('content')
        </div>
    </div>
    <div class="push"></div>
</div>

<footer class="footer">
    Zakupki.com.ua &copy; {{date('Y')}}
</footer>
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="/jquery/ui/jquery-ui.min.js"></script>
<script src="/jquery/dist/jquery.mask.min.js"></script>
<script src="/jquery/dist/jquery.number.min.js"></script>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="/bootstrap/js/bootstrap.min.js"></script>
<script src="/moment/moment-with-locales.min.js"></script>
<script src="/bootstrap/js/bootstrap-datetimepicker.min.js"></script>
<script src="/select2/select2.min.js"></script>

<script src="/js/setup.input.form.js"></script>
<script src="{{asset('js/admin.js')}}" ></script>
<script src="{{asset('js/libs.js')}}" ></script>

</body>
</html>