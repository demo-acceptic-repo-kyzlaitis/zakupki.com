@extends('layouts.admin')

@section('content')
<h3>Перелік осіб які відмінили пропозицію і можуть вимогати повернення коштів</h3>

<div class="forms">
    {!! Form::open(['url' => "/admin/paysystem/repay/search",
                   'method'=>'POST',
                   'enctype'=>'multipart/form-data',
                   'class'=>'form-horizontal',]) !!}
    <div class="col-md-2">
        {!! Form::number('id',null, ['class' => 'form-control', 'placeholder' =>'Id користувача','required'
        ])  !!}
    </div>

    <div class="col-md-3">
        {!! Form::submit(Lang::get('keys.search'),['class'=>'btn btn-danger']) !!}
        {!! Form::close() !!}
    </div>
    <br>
</div>
<br>
        <div class="wrapper">
                <table  class="table table-striped table-bordered sortable tablesorter">
                    <thead>
                    <tr>
                        <th>Id рахунку</th>
                        <th>Id організації</th>
                        <th>Назва організації</th>
                        <th>Сумма</th>
                        <th>Валюта</th>
                        <th>Дата створення рахунку</th>
                        <th>Статус</th>
                        <th>Дії</th>
                    </tr>
                    </thead>
                    @if($unconfirmeds->count())
                    <tbody>
                    <?php
                  // route('admin::organization.edit', $organization->id)
                    ?>

                    @foreach($unconfirmeds as $unconfirmed)
                        <tr>
                            <td>{{$unconfirmed->getOrder()}}</td>
                            <td>{{$unconfirmed->user->organization->id}}</td>
                            <td>{{$unconfirmed->user->organization->name}}</td>
                            <td>{{$unconfirmed->amount}}</td>
                            <td>{{$unconfirmed->currency}}</td>
                            <td>{{$unconfirmed->created_at}}</td>
                            <td>{{$unconfirmed->getStatus()}}</td>
                            <td>
                                <a href="{{ URL::to('admin/paysystem/repay/moneyback/' . $unconfirmed->id) }}" class="btn btn-danger">{{Lang::get('keys.get_back')}}
                                </a>
                            </td>
                        </tr>

                    @endforeach
                    </tbody>
                    @endif
                </table>
                {!!$unconfirmeds->render()!!}
                </div>






@endsection