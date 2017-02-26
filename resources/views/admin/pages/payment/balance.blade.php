@extends('layouts.admin')

@section('content')
    <h3>Баланс користувачів</h3>
        <div class="forms">
        {!! Form::open(['url' => "/admin/paysystem/balance/search",
                       'method'=>'GET',
                       'enctype'=>'multipart/form-data',
                       'class'=>'form-horizontal',]) !!}
        <div class="col-md-2">
            {!! Form::number('user_id',null, ['class' => 'form-control', 'placeholder' =>'Id рахунку','required'
            ])  !!}
        </div>

        <div class="col-md-3">
            {!! Form::submit(Lang::get('keys.search'),['class'=>'btn btn-danger']) !!}

            {!! Form::close() !!}
        </div>
        <br>
    </div>
    <div class="csv_uploader" style="display:none">
        <p>Автоматичне підтвердження платежу</p>
        {!! Form::open(array('url' => 'admin/paysystem/cashless/uploadcsv','method' => 'POST','files'=>'true')) !!}
        <div class="col-md-2">
            {!!Form::file('csv')!!}
        </div>
        <div class="col-md-2">

        </div>
        <div class="col-md-3">
            {!! Form::submit(Lang::get('keys.download'),['class'=>'btn btn-danger']) !!}

            <a href="javascript:" class="btn btn-danger show-cosole">{{Lang::get('keys.hide')}}</a>
        </div>
        {!! Form::close() !!}
        <br>
    </div>

    <br>
    <div class="wrapper">
        <table  class="table table-striped table-bordered sortable tablesorter">
            <thead>
            <tr>
                <th>ID рахунку</th>
                <th>ID організації</th>
                <th>Назва організації</th>
                <th>Баланс</th>
                <th>Валюта</th>
                <th>Дата створення рахунку</th>
                <th>Платежная история</th>
            </tr>
            </thead>

            @if($userbalance->count())

                <tbody>
                @foreach($userbalance as $balance)
                                   <tr>
                        <td>{{$balnce->user_id}}</td>
                        <td><?php if(isset($balance->user->organization->id)){ echo $balance->user->organization->id;}else{ echo 'Нет организации';}?></td>
                        <td><?php if(isset($balance->user->organization->name)){ echo $balance->user->organization->name;}else{ echo 'Нет организации';}?></td>
                        <td>{{$balance->balance}}</td>
                        <td>{{$balance->currency}}</td>
                        <td>{{$balance->created_at}}</td>
                        <td><a href="{{route('admin::paysystem.manualPay', $balance->user_id)}}" class="btn btn-xs btn-info"><span class="glyphicon" aria-hidden="true">$</span></a>
                            <a href="{{route('admin::paysystem.payHistory',$balance->user_id)}}" class="btn btn-xs btn-info"><span class="glyphicon" aria-hidden="true">History</span></a>
                        </td>
                    </tr>

                @endforeach
                </tbody>
                {!!$userbalance->render()!!}
            @endif

        </table>

    </div>






@endsection