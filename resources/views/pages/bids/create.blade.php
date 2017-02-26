@extends('layouts.index')

@section('content')
    <script type="text/javascript">
        var _gaq = _gaq || [];
        _gaq.push(['_setAccount', 'UA-36251023-1']);
        _gaq.push(['_setDomainName', 'jqueryscript.net']);
        _gaq.push(['_trackPageview']);

        (function() {
            var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
            ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
            var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
        })();

    </script>
    {{--Editing Section Start--}}
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <?php $tender = $entity->tender;?>
                @include('share.component.title')
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                @include('share.component.tabs')
            </div>
        </div>
        <section class="registration container">
            <h4>Реєстрація пропозиції</h4>
            @if($errors->has())
                <div class="alert alert-danger" role="alert">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            {!! Form::open(['url' => "/bid",
            'method'=>'POST',
            'enctype'=>'multipart/form-data',
            'class'=>'form-horizontal', 'id' => 'bid']) !!}
            <fieldset>
                @include('pages.bids.component.form')
                <hr>

                <div class="form-group">
                    <div class="col-lg-12 text-center">
                        <div class="countdown">
                            <h4>До закінчення періоду подачі пропозицій залишилось <span class="days">00</span> днів(день) <span class="hours">00</span> годин(а) <span class="minutes">00</span> хвилин(а) <span class="seconds">00</span> секунд</h4>
                        </div>
                        <script type="text/javascript">
                            $('.countdown').downCount({
                                date: '{!!$tenderDateEnd!!}',
                                offset: {{(date('Z') / 3600)}}
                            }, function () {
                                $('.countdown h4').text('Увага! період подачі пропозицій завершено')
                                $('.btn-success').attr('disabled','disabled');

                            });
                        </script>
                        </body>

                    </div>
                    <div class="col-lg-12 text-center">
                        @if ($tender->mode == 1)
                        @if (Auth::user()->organization->confirmed && $paymentAmount > $balance)
                            <div class="container"><div class="alert alert-danger" role="alert">
                                    <p>На вашому рахунку недостатньо коштів для участі у закупівлі.</p>
                                    <p>Вартість участі становить: {{number_format($paymentAmount, 2, '.', '')}} {{$tender->currency->currency_code}}</p>
                                    <p>Ваш баланс: {{$balance}} {{$tender->currency->currency_code}}</p><br>
                                    <a href="{{route('Payment.pay')}}" class="btn btn-success">{{Lang::get('keys.create_payment_account')}}</a>
                                </div></div>

                        @else
                            <div class="container">
                                <div class="alert alert-warning" role="alert">
                                    <p>Вартість участі становить {{number_format($paymentAmount, 2, '.', '')}} {{$tender->currency->currency_code}}</p><br>
                                    {!! Form::submit(Lang::get('keys.create_bid'),['class'=>'btn btn-success']) !!}
                                </div>
                            </div>

                        @endif
                        @else
                            {!! Form::submit(Lang::get('keys.create_bid'),['class'=>'btn btn-success']) !!}
                        @endif
                    </div>
                </div>
            </fieldset>
            {!! Form::close() !!}







        </section>
        {{--Editing Section End--}}
    </div>
    
    <script>
        $(function () {
            $('.create-bid').click(function (event) {
                event.preventDefault();
                $(this).attr('disabled','disabled');
                $(this).attr('value','Завантаження...');

                var data = new FormData($('#bid')[0]);


                $('.file-input').each(function (index, elem) {
                    data.append('bid[files][]', $(elem)[0].files[0]);
                });


                $.ajax({
                    xhr: function() {
                        var xhr = new window.XMLHttpRequest();

                        xhr.upload.addEventListener("progress", function(evt) {
                            if (evt.lengthComputable) {
                                var percentComplete = evt.loaded / evt.total;
                                percentComplete = parseInt(percentComplete * 100);
                                $('.progress-bar').css('width', percentComplete + '%');
                                $('.progress-bar').html(percentComplete + '%');


                            }
                        }, false);

                        return xhr;
                    },
                    url: "/bid",
                    type: 'POST',
                    data: data,
                    cache: false,
                    dataType: 'json',
                    processData: false, // Don't process the files
                    contentType: false, // Set content type to false as jQuery will tell the server its a query string request
                    success: function(data, textStatus, jqXHR)
                    {
                        if(typeof data.error === 'undefined')
                        {
                            document.location = '/bid/list';
                        }
                        else
                        {
                            console.log(data);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown)
                    {
                        alert('Не вдалося подати пропозицію. Зверніться до адміністратора.');
                        $('.create-bid').attr('disabled', false);
                        $('.create-bid').attr('value','Подати пропозицію');
                        $('.progress-bar').css('width', '0%');
                        $('.progress-bar').html('0%');


                    }
                });
            });
        });

    </script>


@endsection