@extends('layouts.admin')

@section('content')
<h3>Перелік осіб які очікують поповнення рахунку</h3>

<div class="console">
<a href="javascript:" class="btn btn-danger search-show">{{Lang::get('keys.search')}}</a>
<a href="javascript:" class="btn btn-danger csv-show">{{Lang::get('keys.add_csv_file')}}</a>
</div>


<div class="forms" style="display:none">
    {!! Form::open(['url' => "/admin/paysystem/cashless/search",
                   'method'=>'POST',
                   'enctype'=>'multipart/form-data',
                   'class'=>'form-horizontal',]) !!}
    <div class="col-md-2">
        {!! Form::number('id',null, ['class' => 'form-control', 'placeholder' =>'Id рахунку','required'
        ])  !!}
    </div>

    <div class="col-md-3">
        {!! Form::submit(Lang::get('keys.search'),['class'=>'btn btn-danger']) !!}
        <a href="javascript:" class="btn btn-danger show-cosole">{{Lang::get('keys.hide')}}</a>
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
                        <th>Id рахунку</th>
                        <th>ID організації</th>
                        <th>Назва організацїї</th>
                        <th>Сума</th>
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
                            <td>{{$unconfirmed->status_ps}}</td>
                            <td>
                                <a href="{{ URL::to('admin/paysystem/cashless/confirm/' . $unconfirmed->id) }}" class="btn btn-danger">{{Lang::get('keys.create_payment_account')}}</a>
                            </td>
                        </tr>

                    @endforeach
                    </tbody>
                    @endif
                </table>
                {!!$unconfirmeds->render()!!}
                </div>






@endsection