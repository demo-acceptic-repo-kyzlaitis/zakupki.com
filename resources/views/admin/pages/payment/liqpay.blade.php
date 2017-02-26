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
                <th>Статус Наш</th>
                <th>Статус Платежной системы</th>
                <th>Id платежа в системе LiqPay</th>
                <th>Сума пополнения</th>
                <th>Комиссия с получателя в валюте платежа</th>
                <th>Комиссия с отправителя в валюте платежа</th>
                <th>Валюта</th>
                <th>Дата создания</th>
                <th>Дата последнего обновления</th>
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
                        <td><?php if(isset($unconfirmed->user->organization->id)){ echo $unconfirmed->user->organization->id;}else{ echo 'Нет организации';}?></td>
                        <td><?php if(isset($unconfirmed->user->organization->name)){ echo $unconfirmed->user->organization->name;}else{ echo 'Нет организации';}?></td>
                        <td>{{$unconfirmed->getOurStatus()}}</td>
                        <td>{{$unconfirmed->status_ps}}</td>
                        <td>{{$unconfirmed->transaction_id}}</td>
                        <td>{{$unconfirmed->amount}}</td>
                        <td>{{$unconfirmed->receiver_commission}}</td>
                        <td>{{$unconfirmed->sender_commission}}</td>
                        <td>{{$unconfirmed->currency}}</td>
                        <td>{{$unconfirmed->created_at}}</td>
                        <td>{{$unconfirmed->updated_at}}</td>
                    </tr>
                @endforeach
                </tbody>
            @endif
        </table>
        {!!$unconfirmeds->render()!!}
    </div>






@endsection