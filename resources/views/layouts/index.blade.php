<!DOCTYPE html>
<meta name="csrf-token" content="{{ csrf_token() }}">
<html lang="en">
@include('partials.common.header')

<body>
<!-- Yandex.Metrika counter -->
<script type="text/javascript">
(function (d, w, c) {
(w[c] = w[c] || []).push(function() {
try {
w.yaCounter42364139 = new Ya.Metrika({
id:42364139,
clickmap:true,trackLinks:true,
accurateTrackBounce:true,
webvisor:true
});
} catch(e) { }
});
var n = d.getElementsByTagName("script")[0],
s = d.createElement("script"),
f = function () { n.parentNode.insertBefore(s, n); };
s.type = "text/javascript";
s.async = true;
s.src = "https://mc.yandex.ru/metrika/watch.js";
if (w.opera == "[object Opera]") {
d.addEventListener("DOMContentLoaded", f, false);
} else { f(); }
})(document, window, "yandex_metrika_callbacks");
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/42364139" style="position:absolute;
left:-9999px;" alt="" /></div></noscript>
<!-- Yandex.Metrika counter -->
<!-- Google Tag Manager -->
<noscript><iframe src="//www.googletagmanager.com/ns.html?id=GTM-P3FQLZ"
                  height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
            new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            '//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-P3FQLZ');</script>
<!-- End Google Tag Manager -->
@include('layouts.menu.menu')

@include('share.component.modal-alert', ['modalNamespace' => 'maxFilesAmount', 'modalTitle' => 'Великий розмір файлу', 'modalMessage' => 'Розмір файлу не повинен перевищувати 45 Мб'])

<div style="padding-top: 80px" class="wrapper">

    {{--@if (Auth::check() && Auth::user()->organization && Auth::user()->organization->type == 'customer')--}}
    {{--<div style="padding-top: 30px;">--}}
        {{--<div class="container">--}}
            {{--<div class="alert alert-success">--}}
                {{--<p>--}}
                    {{--Шановні користувачі, 01.02.2017 об 11.00 відбудеться безкоштовний вебінар ЗАМОВНИКАМ: Початок роботи в системі PROZORRO. Робота з планами та допороговими закупівлями на майданчику Zakupki.UA--}}
                    {{--<br><br> Реєстрація за посиланням <a href="https://room.etutorium.com/registert/6/4542934b5a2d551052c18ad65a2d551052c1beec">https://room.etutorium.com/registert/6/4542934b5a2d551052c18ad65a2d551052c1beec</a>.--}}
                {{--</p>--}}
            {{--</div>--}}
        {{--</div>--}}
    {{--</div>--}}
    {{--@endif--}}

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
    @if (Session::has('flash_modal'))
        <style>@media (min-width: 768px) {
                .modal-dialog {
                    width: 30%;
                }
            }</style>
        <!-- Modal -->
        <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <?php echo Session::get('flash_modal') ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary text-center" data-dismiss="modal">{{Lang::get('keys.ok')}}</button>
                    </div>
                </div>
            </div>
        </div>
        <script>
            $(function(){
                $('#myModal').delay( 5000 ).modal();
            })
        </script>
    @endif
    <script>
        $('document').ready(function(){
            $('#modalne').modal();
        });
    </script>

    <?php $notification = \App\Http\Controllers\NotificationController::viewNewMsg();?>

    @if($notification && !in_array(Auth::user()->email, ['andreynichik@gmail.com', 'illia.kyzlaitis.cv@gmail.com', 'customer@gmail.com', 'supplier1@gmail.com', 'supplier2@gmail.com', 'test_tender_owner_viewer@gmail.com']))

        <a href="#" class="click" data-toggle="modal" data-target="#agreement-modal"></a>
        <!-- Modal -->
        <style>
            @media (min-width: 768px) {
                .modal-dialog {
                    width: 60%;
                }
            }
        </style>
        <div class="modal fade" id="agreement-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel">{{$notification->title}}</h4>
                    </div>
                    <div class="modal-body">
                        <div class="input-group input-group-lg">
                            {!!$notification->text!!}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" value="{{$notification->id}}" class="btn btn-primary readble" data-dismiss="modal">{{Lang::get('keys.viewed')}}</button>
                    </div>
                </div>
            </div>
        </div>
        <script>
            $(function(){
                $('.click').trigger('click');
            })
        </script>
    @endif


    @yield('content')
    <div class="push"></div>
</div>
<footer class="footer">
    <div class="fusion-copyright-content">

        <div class="fusion-copyright-notice">Служба підтримки Zakupki UA: Безкоштовний дзвінок: 0 800 75 06 85
            <div>Copyright &copy; 2016&mdash;{{date('Y')}} ТОВ "ЗАКУПІВЛІ ЮА". Усі права захищені. | <a href="http://lp.zakupki.com.ua">Zakupki UA - Електронний майданчик учасник системи публічних закупівель Prozorro.</a><br>
                <a href="http://support.zakupki.com.ua/">Служба підтримки Zakupki UA:</a>(068) 492-14-28.</div>
        </div>

        <div style="margin-top: 15px">
            <div class="fusion-social-networks boxed-icons">
                <div class="fusion-social-networks-wrapper">
                    <a style="margin-left: 5px" href="https://www.facebook.com/lp.zakupki.com.ua/" target="_blank" rel="nofollow">
                        <img src="/i/facebook.png" width="32">
                    </a>
                    <a style="margin-left: 5px"  href="https://lp.zakupki.com.ua/sitemap_index.xml" target="_blank" rel="nofollow" data-placement="top" data-title="Rss" data-toggle="tooltip" title="" data-original-title="Rss">
                        <img src="/i/rss.png" width="32">
                    </a>
                    <a  style="margin-left: 5px" href="mailto:support@zakupki.com.ua" target="_self" rel="nofollow" data-placement="top" data-title="Email" data-toggle="tooltip" title="" data-original-title="Email">
                        <img src="/i/email.png" width="32">
                    </a>
                </div>
            </div>
        </div>

    </div>
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
<script src="{{asset('js/notification.js')}}"></script>
<script src="{{asset('js/public.js')}}"></script>
<script src="/js/jquery.countdown.js"></script>
@yield('foot')
<div class="tl-call-catcher">
    <!--BANNER ON SITE-->
</div>
<!--BEGIN PHONET CODE {literal}-->
<script>var telerWdWidgetId="1363c0dc-37ef-4c46-b83e-7d8fdd223052";var telerWdDomain="zakupki.phonet.com.ua";</script>
<script src="//zakupki.phonet.com.ua/public/widget/call-catcher/lib-v3.js"></script>
<!--{/literal} END PHONET CODE -->

</body>
</html>


