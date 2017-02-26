@extends('layouts.admin')

@section('content')
    <h3>Історія</h3>
    <br>
    <div class="wrapper">
        <table class="table table-striped table-bordered sortable tablesorter">
            <thead>
            <tr>
                <th>Id рахунку</th>
                <th>ID організації</th>
                <th>Назва організації</th>
                <th>Сума</th>
                <th>Валюта</th>
                <th>Дата створення рахунку</th>
                <th>Статус</th>
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
                        <td>{{$unconfirmed->getOrder()}}</td>
                        <td>{{$unconfirmed->user->organization->id}}</td>
                        <td>{{$unconfirmed->user->organization->name}}</td>
                        <td>{{$unconfirmed->amount}}</td>
                        <td>{{$unconfirmed->currency}}</td>
                        <td>{{$unconfirmed->created_at}}</td>
                        <td>{{$unconfirmed->getStatus()}}</td>
                        <td>{{$unconfirmed->who_add}}</td>
                    </tr>

                @endforeach
                </tbody>
            @endif
        </table>
        {!!$unconfirmeds->render()!!}
    </div>






@endsection