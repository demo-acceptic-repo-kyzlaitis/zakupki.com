<nav class="navbar navbar-default navbar-fixed-top">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                    data-target="#bs-example-navbar-collapse-1">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" style="padding: 3px 15px" href="{{route('tender.list')}}"><img src="/i/logo.png" style="height: 44px"></a>
        </div>
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            @if(Auth::check())
                @if (Auth::user()->organization)
                    <?php $organization_type = Auth::user()->organization->type;
                        $path = Request::path();
                        $USER_ID = Auth::user()->organization->id;
                        if (Auth::user()->organization->mode == '0'){
                            $mode_status = 'test';

                        }else{
                            $mode_status = 'active';
                        }

                    ?>
                    <script type="text/javascript"> ga('set', 'dimension1', '{{$mode_status}}' );
                                                    ga('set', 'dimension2', '{{$organization_type}}' );
                                                    ga('set', 'userId', '{{$USER_ID}}');
                                                    ga('send', 'pageview', '{{$path}}');
                    </script>
                @endif

                <ul class="nav navbar-nav">
                    @if (Auth::user()->organization && Auth::user()->organization->type == 'supplier')  {{-- && Auth::user()->organization->confirmed--}}
                        <li><a href="{{route('Payment.pay')}}">Баланс</a></li>
                    @endif
                    @if (Auth::user()->organization && Auth::user()->organization->type == 'customer')
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Мої закупівлі <span class="caret"></span></a>
                            <ul class="dropdown-menu">
                                <li><a href="{{route('tender.create')}}">Додати закупівлю</a></li>
                                <li><a href="{{route('tender.index')}}">Мої закупівлі</a></li>
                                <li role="separator" class="divider"></li>
                                <li><a href="{{route('award.index')}}">Кваліфікація</a></li>
                            </ul>
                        </li>
                    @endif

                    @if (Auth::user()->organization && Auth::user()->organization->type == 'supplier')

                        <li><a href="{{route('bid.list')}}">Мої пропозиції</a></li>
                        <li><a href="{{route('questions.userlist')}}">Мої запитання</a></li>
                    @elseif(Auth::user()->organization && Auth::user()->organization->type == 'customer')
                        <li><a href="{{route('questions.Customerslist')}}">
                            @if(!empty(Auth::user()->organization))
                                <?php $questions = Auth::user()->organization->myQuestions()->notAnswered();?>
                                @if ($questions->count())
                                    <?php
                                    $i = 0;
                                    foreach ($questions->get() as $question){
                                        if($question->tender->canQuestion()){
                                            $i++;
                                        }
                                    }?>

                                    @if ($i > 0)<span style="color: red">Запитання ({{$i}})</span> @else Запитання @endif
                                @else
                                    Запитання
                                @endif
                            @endif
                        </a></li>
                        @endif
                    @if (Auth::user()->organization && Auth::user()->organization->type == 'customer')<li><a href="{{route('claim.list')}}">
                            @if (Auth::user()->organization && Auth::user()->organization->activeComplaints()->count())
                                <span style="color: red">Вимоги ({{Auth::user()->organization->activeComplaints()->count()}}) </span>
                            @else
                                Вимоги
                            @endif
                        </a></li>
                    @endif
                    @if (Auth::user()->organization && Auth::user()->organization->type == 'supplier')<li><a href="{{route('claim.list')}}">
                            @if (Auth::user()->organization && Auth::user()->organization->activeComplaints()->count())
                                <span style="color: red">Вимоги ({{Auth::user()->organization->activeComplaints()->count()}}) </span>
                            @else
                                Вимоги
                            @endif
                        </a></li>
                    @endif


                    @if (Auth::user()->organization && Auth::user()->organization->type == 'customer')
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Планування <span class="caret"></span></a>
                            <ul class="dropdown-menu">
                                <li><a href="{{route('plan.create')}}">Створити план</a></li>
                                <li><a href="{{route('plan.list')}}">Мої плани закупівель</a></li>
                                <li><a href="{{route('plan.createImport')}}">Імпорт планів</a></li>
                            </ul>
                        </li>
                    @endif




                        <li>&nbsp;</li><li>&nbsp;</li><li>&nbsp;</li>
                        <li style="margin-left: 10px; margin-right: 10px">
                            <p class="navbar-btn">
                                <?php
                                    $organization = Auth::user()->organization;
                                ?>
                                @if ($organization && $organization->type !== 'guest')
                                    <b>Режим роботи </b>: <input @if ($organization->confirmed == 0) disabled @endif id="mode" @if ($organization->mode == 1) checked @endif type="checkbox" data-toggle="toggle" data-on="Реальний" data-off="Тестовий" data-onstyle="success" data-offstyle="danger">
                                    {{--<a href="{{route('organization.mode', ['m' => !$organization->mode])}}" class="btn @if ($organization->mode == 1) btn-success @else btn-danger @endif">@if ($organization->mode == 0) Перейти в режим реальних закупівель @else Перейти в режим тестових закупівель @endif</a>--}}
                                @endif
                            </p>
                        </li>
                    {{--@if(Auth::user()->organization && Auth::user()->organization->type === 'supplier')--}}
                        {{--<li><a href="{{route('user.offer')}}">Договір</a></li>--}}
                    {{--@endif--}}
                </ul>
            @else
                <ul class="nav navbar-nav">
                    <!--<li><a href="{{route('tender.list')}}">Електронний майданчик</a></li>-->
                </ul>
            @endif

            @if(Auth::check())
                <ul class="nav navbar-nav navbar-right">

                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                            @if(isset(Auth::user()->organization))О/р {{Auth::user()->organization->id}} @else Дані користувача @endif <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="{{route('user.edit')}}">Особисті дані</a></li>
                            <li><a href="{{route('organization.edit')}}">Дані організації</a></li>
                            @if(isset(Auth::user()->organization) && Auth::user()->organization->type == 'supplier')
                                {{--todo раскоментить при завершении поисковых агентов--}}
                                <li>
                                    <a href="{{route('agent.index')}}">Пошукові агенти</a>
                                </li>
                            @endif
                        </ul>
                    </li>
                    @if(Auth::user()->organization && Auth::user()->organization->type !== 'guest')
                        <li>
                            <a href="{{route('notification.index')}}">Повідомлення
                                @if (\App\Model\Notification::personal()->notReaded()->count())
                                    <span class="label label-danger label-as-badge">
                                            {{\App\Model\Notification::personal()->notReaded()->count()}}
                                    </span>
                                @endif
                            </a>
                        </li>
                    @endif
                    <li><p class="navbar-btn"><a class="btn btn-default" style="margin-right: 10px" href="{{route('user.logout')}}">{{Lang::get('keys.logout')}}</a></p></li>
                </ul>
            @else
                <ul class="nav navbar-nav navbar-right">

                    <li><p class="navbar-btn"><a class="btn btn-default" href="{{route('user.login')}}">{{Lang::get('keys.login')}}</a></p></li>
                    <li>&nbsp;</li>
                    <li><p class="navbar-btn"><a class="btn btn-danger" href="{{route('user.register')}}">{{Lang::get('keys.register')}}</a></p></li>
                    <li>&nbsp;</li>
                </ul>
            @endif
        </div>
    </div>
</nav>



<!-- Modal -->
<div class="modal fade" id="modalMode" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body">
                @if (Auth::check() && Auth::user()->organization && Auth::user()->organization->type == 'customer')
                    <?php echo trans('messages.agreement.customer');?>
                @else
                    <h2>Шановний Користувач електронного майданчика "Zakupki UA",</h2>
                    <p>для використання електронного майданчика "Zakupki UA" з метою участі у якості учасника у закупівлях у відповідності до законодавства у сфері публічних закупівель, кожен Користувач, крім реєстрації/авторизації на електронному майданчику "Zakupki UA", має:
                    <ul>
                        <li>прийняти (акцептувати) Пропозицію ТОВАРИСТВА З ОБМЕЖЕНОЮ ВІДПОВІДАЛЬНІСТЮ "ЗАКУПІВЛІ ЮА" укласти Договір про надання послуг (приєднатися до Договору) шляхом здійснення оплати з власного поточного (розрахункового) рахунку за наданим Оператором рахунком, відповідно до умов Договору;</li>
                        <li>пройти ідентифікацію та отримати від Оператора доступ до електронної системи закупівель.</li>
                    </ul>
                    </p>

                    <p>
                        Користувачі мають змогу ознайомитись з чинними редакціями Регламенту електронного майданчика
                        "Zakupki UA" і Тарифів електронного майданчика "Zakupki UA" за посиланнями:
                        <a href="https://lp.zakupki.com.ua/reglament">https://lp.zakupki.com.ua/reglament</a>
                        і
                        <a href="https://lp.zakupki.com.ua/pricing">https://lp.zakupki.com.ua/pricing</a>.
                    </p><br>
                    <p>
                        З повагою,<br>
                        Служба підтримки Zakupki UA<br>
                        support@zakupki.com.ua
                    </p>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">{{Lang::get('keys.ok')}}</button>
            </div>
        </div>
    </div>
</div>





<script>
    $(function(){
        $('#mode').on('change', function(e) {
            if ($(this).attr('checked')) {
                document.location = '{{route('organization.mode', ['m' => 0])}}';
            } else {
                document.location = '{{route('organization.mode', ['m' => 1])}}';
            }
        });
        $('.toggle.btn[disabled=disabled]').on('click', function(e) {
            $('#modalMode').modal();
        });
        $('.only-confirmed').on('click', function(e) {
            $('#modalMode').modal();
            return false;
        });
    })
</script>