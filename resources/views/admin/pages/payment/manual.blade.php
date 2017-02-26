@extends('layouts.admin')

@section('content')
    <h3>Ручное пополнение баланса</h3>
    <br>
    <div class="wrapper">
        {!! Form::open(['url' => route('admin::payments.add'),
                 'method'=>'POST',
                 'enctype'=>'multipart/form-data',
                 'class'=>'form-horizontal',]) !!}
        <table class="table table-striped table-bordered sortable tablesorter">
            <thead>
            <tr>
                <td>ID Баланса</td>
                <td>Текущий баланс </td>
                <td>Пополнение</td>
                <td>Новый Cчет</td>
                <td>Действие</td>
            </tr>
            <tr>
                <td>{{$unconfirmeds->user_id}}</td>
                <td>{{$unconfirmeds->amount}}</td>
                <td>{!! Form::hidden('user_id',$unconfirmeds->user_id, ['class' => 'form-control', 'placeholder' =>'Сумма',])  !!}
                    {!! Form::number('amount',null, ['class' => 'form-control', 'placeholder' =>'Сумма','required'])  !!}</td>
                <td> <input type="checkbox" name="order" class="form-control" name="order"></td>
                <td>{!! Form::submit(Lang::get('keys.fill'),['class'=>'btn btn-danger']) !!}
                </td>

            </tr>
            </thead>
                </tbody>
            {!! Form::close() !!}
        </table>
    </div>






@endsection