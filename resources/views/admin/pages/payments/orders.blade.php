@extends('layouts.admin')

@section('content')


    <h2>Счета</h2>
    <div class="well">
        {!! Form::close() !!}
    </div>

    @if($orders->count())
        <div class="wrapper">
            <div class="">
                <p>Найдено: {{$orders->total()}}</p>
                <table class="table table-striped table-bordered sortable tablesorter table-notes">
                    <thead>
                    <tr>
                        <th>Номер</th>
                        <th>Сумма</th>
                        <th>Статус</th>

                        <th>Дата</th>
                        <th></th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach($orders as $order)
                        <tr>
                            <td>{{$order->number}}</td>
                            <td>{{$order->amount}}</td>
                            <td>@if ($order->status == 'new') Новый @elseif($order->status == 'payed') Оплачен @endif </td>
                            <td>{{$order->created_at}}</td>
                            <td>@if ($order->status == 'new')<a class="btn btn-success" href="/admin/payments/pay?order_id={{$order->id}}">{{Lang::get('keys.pay')}}</a>@endif</td>
                        </tr>

                    @endforeach
                    </tbody>
                </table>

            </div>

            <div class="text-center">
                {!! $orders->render() !!}
            </div>
        </div>

    @else
        <div class="text-center">За Вашим запитом нічого не знайдено</div>
    @endif

@endsection



