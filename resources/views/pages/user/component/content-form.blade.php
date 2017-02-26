
<div class="form-group">
    <label for="title" class="col-sm-4 control-label">Ім'я</label>
    <div class="col-lg-4">
        {!! Form::text('name',null,
        ['class' => 'form-control', 'placeholder' =>'', 'required'  ]) !!}
    </div>
</div>

<div class="form-group">
    <label for="title" class="col-sm-4 control-label">Email</label>
    <div class="col-lg-4">
        {!! Form::email('email',null,
           ['id'=>'inputEmail', 'class' => 'form-control', 'placeholder' =>'',
            ( Auth::check() ? 'disabled' : ''),
            'required'
           ]) !!}
    </div>
</div>

@if(!Auth::check())

    <div class="form-group">
        <label for="title" class="col-sm-4 control-label">Пароль</label>
        <div class="col-lg-4">
            {!! Form::password('password',
                ['id'=>'inputPassword', 'class' => 'form-control', 'placeholder' =>'',
                    'required'
                ]) !!}
        </div>
    </div>



    <div class="form-group">
        <label for="title" class="col-sm-4 control-label">Підтвердження паролю</label>
        <div class="col-lg-4">
            {!! Form::password('password_confirmation',
                ['id'=>'inputPasswordConfirmation', 'class' => 'form-control', 'placeholder' =>'',
                    'required'
                ]) !!}
        </div>
    </div>

    <div class="form-group">
        <div class="col-lg-4"></div>
        <div class="col-lg-4">{!! Form::checkbox('agreement',1, false, [ 'placeholder' =>'', ''])  !!} <a href="#" data-toggle="modal" data-target="#agreement-modal">Згода суб’єкта персональних даних.</a>
            </div>
    </div>



    <!-- Modal -->
    <style>@media (min-width: 768px) {
            .modal-dialog {
                width: 60%;
            }
        }</style>
    <div class="modal fade" id="agreement-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Згода суб’єкта персональних даних</h4>
                </div>
                <div class="modal-body">
                    <div class="input-group input-group-lg">

                            <p>Я, який/яка заповнює поля реєстраційної форми електронного майданчика "Zakupki UA", що розміщений на веб-сайті zakupki.com.ua
                                (далі за текстом – "Веб-сайт "), під час реєстрації на електронному майданчику "Zakupki UA",
                                у відповідності до Закону України від 1 червня 2010 року №2297-VI "Про захист персональних даних"
                                (далі за текстом – "Закон") шляхом проставляння галочки (відмітки у вигляді двох рисок, що сходяться
                                внизу, утворюючи гострий кут) у позиції для відмітки (check box) про Згоду суб’єкта персональних даних:</p>
                            <p></p>
                            <ol>
                                <li>надаю ТОВАРИСТВУ З ОБМЕЖЕНОЮ ВІДПОВІДАЛЬНІСТЮ "ЗАКУПІВЛІ ЮА", місцезнаходження якого Україна, 01133, місто Київ, бульвар Лесі Українки, будинок 5-А, (далі за текстом – "Товариство") дозвіл на обробку своїх персональних даних (далі за текстом – "Дозвіл") відповідно до сформульованої мети їх обробки, а саме – для забезпечення реалізації визначеної у Статуті Товариства господарської діяльності, – у складі своїх персональних даних, що вказуються на Веб-сайті / електронному майданчику "Zakupki UA", який є джерелом збирання таких даних після уведення цих даних на Веб-сайті / електронному майданчику "Zakupki UA";</li>
                                <li>підтверджую, що форма, у якій висловлено (виражено) Дозвіл, дає змогу зробити однозначний висновок про надання Згоди суб’єкта персональних даних, а також підтверджую, що не маю претензій будь-якого характеру до такої форми;</li>
                                <li>зобов’язуюсь надавати точні та достовірні персональні дані, а також підтримувати їх в актуальному стані;</li>
                                <li>розумію, що на момент надання цієї Згоди (Дозволу) база персональних даних може перебувати на стадії формування;</li>
                                <li>розумію, що даний текст Згоди, хоча і розміщено на Веб-сайті, але не примушує Товариство включати мої персональні дані до бази персональних даних;</li>
                                <li>розумію, що у зв’язку із наданням цієї Згоди (Дозволу) мої персональні дані можуть бути доступними третім особам, перебувати на Веб-сайті / електронному майданчику "Zakupki UA", у тому числі у відкритому доступі для будь-яких відвідувачів / користувачів Веб-сайту / електронного майданчика "Zakupki UA", та/або бути доступними для осіб, які користуються електронною системою закупівель, зокрема, але не виключно – на веб-порталі Уповноваженого органу з питань закупівель, електронному майданчику "Zakupki UA" та інших авторизованих електронних майданчиках, які є частинами (елементами) електронної системи закупівель;</li>
                                <li>повідомляю, що мені відомі права, передбачені Законом, джерела збирання, місцезнаходження своїх персональних даних, мета обробки персональних даних, місцезнаходження володільця персональних даних;</li>
                                <li>розумію, що обробка персональних даних здійснюється відкрито і прозоро із застосуванням засобів та у спосіб, що відповідають визначеним цілям такої обробки.</>
                            </ol>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">{{Lang::get('keys.close')}}</button>
                </div>
            </div>
        </div>
    </div>
@endif
