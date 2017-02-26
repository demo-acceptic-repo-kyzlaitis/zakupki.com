@extends('layouts.index')
@section('content')
    <div class="registration container">
        <h2>Поповнення особового рахунку</h2>
        <hr>
        <br>
        {!! Form::open(array('url' => '/paysystems/index','method' => 'POST')) !!}
        <fieldset>
            <div class="form-group">
                <label for="type" class="col-sm-4 control-label">Поточний баланс</label>

                <div class="col-lg-4">
                    <span style="color:green">  {{$balance}} UAH </span>
                </div>
            </div>
            <br>
            <br>
            @if(isset($services))
                <div class="form-group">
                    <label for="type" class="col-sm-4 control-label">Виберіть спосіб оплати</label>

                    <div class="col-lg-4">
                        {!! Form::select('service_id',$services, null,
                        ['class' => 'form-control type-select','required'
                        ]) !!}
                    </div>
                </div>
                <br>
                <br>
            @endif
            <div class="form-group">
                <label for="type" class="col-sm-4 control-label">Сума поповнення в гривнях</label>

                <div class="col-lg-4">
                    {!! Form::text('amount',null,
                       ['id'=>'amount', 'class' => 'form-control', 'placeholder' =>'00.00','pattern'=>'\d+(\.\d{2})?',
                       'required'
                       ]) !!}
                </div>
            </div>
            <br>
            <br>

            <div class="form-group">
                {!! Form::submit(Lang::get('keys.create_payment_account'),['class'=>'btn btn-danger']) !!}
            </div>
            {!! Form::close() !!}
            <br>
        </fieldset>

        <div >
            <h3>Рахунки</h3>
            @if ($orders->count())
                <table class="table table-striped table-bordered sortable tablesorter table-notes">
                    <thead>
                    <tr>
                        <th>Номер</th>
                        <th>Сума</th>
                        <th>Дата</th>
                        <th></th>
                    </tr>
                    </thead>
                <tbody>
                @foreach($orders as $order)
                    <tr>
                        <td>{{$order->number}}</td>
                        <td>{{$order->amount}}</td>
                        <td>{{$order->created_at}}</td>
                        <td>
                            <a class="btn btn-success" target="_blank" href="{{route('Payment.print', ['id' => $order->id])}}"><span class="glyphicon glyphicon-print" aria-hidden="true"></span> {{Lang::get('keys.print')}}</a>
                            <a class="btn btn-success" target="_blank" href="{{route('Payment.saveAsPdf', ['id' => $order->id])}}"><span class="glyphicon glyphicon-print" aria-hidden="true"></span> {{Lang::get('keys.save_as_pdf')}}</a>
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>

            @endif
            <a href="https://lp.zakupki.com.ua/pricing" target="_blank">Перейти на сторінку Тарифів</a>
        </div>


        <br>
        <br>
        <hr>
        <div >
            <h3>Історія платежів</h3>
            @if($transactions->count())
                <table class="table table-striped table-bordered sortable tablesorter table-notes">
                    <thead>
                    <tr>
                        <th>Ідентифікатор</th>
                        <th>Тип операції</th>
                        <th>Сума</th>
                        <th>Валюта</th>
                        <th>Підстава</th>
                        <th>Дата операції</th>
                        <th>Статус</th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach($transactions as $transaction)
                        <tr>
                            <td>{{$transaction->id}}</td>
                            <td>
                                @if ($transaction->amount <= 0)
                                    <span class="label label-danger">Списання</span>
                                @else
                                    <span class="label label-success">Поповнення</span>
                                @endif
                            </td>
                            <td>
                                @if ($transaction->amount <= 0)
                                    <span style="color: red">{{$transaction->amount}}</span>
                                @else
                                    <span style="color: green">{{$transaction->amount}}</span>
                                @endif
                            </td>
                            <td>UAH</td>
                            <td>
                                @if($transaction->payment_service_id == 0)
                                    @if($transaction->payment_type == 'App\Model\Tender')
                                        оплата за участь <a href="{{route('tender.show', $transaction->payment->id)}}">{{$transaction->payment->title}}</a>
                                    @endif

                                    @if($transaction->payment_type == 'App\Model\Lot')
                                        оплата за участь  <a href="{{route('tender.show', $transaction->payment->tender_id)}}">{{$transaction->payment->title}}</a>
                                    @endif
                                @endif
                            </td>
                            <td>{{$transaction->created_at}}</td>
                            <td><span class="label label-success">Завершено</span></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                {!! $transactions->render() !!}
            @endif

        </div>
    </div>


@endsection