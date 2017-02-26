@extends('layouts.admin')

@section('content')
    <h3>Історія платіжної систему рахунку №{{$nom}}</h3>
    <br>
    <div class="wrapper">
        <table class="table table-striped table-bordered sortable tablesorter table-notes">
            <thead>
            <tr>
                <th>Id рахунку</th>
                <th>ID організації</th>
                <th>Назва організації</th>
                <th>Сума</th>
                <th>Валюта</th>
                <th>Дата створення рахунку</th>
                <th>Платежная система</th>
                <th>Статус в платежной системе</th>
                <th>Статус в нашей системе</th>
                <th>Тип Действия</th>
                <th>Кто підтвердив</th>
            </tr>
            </thead>
            @if($unconfirmeds->count())
                <tbody>
                <?php
                // route('admin::organization.edit', $organization->id)
                ?>

                @foreach($unconfirmeds as $unconfirmed)
                    <tr>
                        <td>{{$unconfirmed->id}}</td>
                        <td>{{$unconfirmed->user->organization->id}}</td>
                        <td>{{$unconfirmed->user->organization->name}}</td>
                        <td>{{$unconfirmed->amount}}</td>
                        <td>{{$unconfirmed->currency}}</td>
                        <td>{{$unconfirmed->created_at}}</td>
                        <td>{{$unconfirmed->getPaymentSystem()}}</td>
                        <td>{{$unconfirmed->status_ps}}</td>
                        <td>{{$unconfirmed->getOurStatus()}}</td>
                        <td>{{$unconfirmed->getMove()}}</td>
                        <td>{{$unconfirmed->who_add}}</td>
                    </tr>

                @endforeach
                </tbody>
            @endif
        </table>
        {!!$unconfirmeds->render()!!}
    </div>






@endsection