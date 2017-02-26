@extends('layouts.admin')

@section('content')


    <h2>Транзакции</h2>
    <div class="well">
        {!! Form::open(['url' => route('admin::payments.transactions'),
        'method'=>'GET',
        'class'=>'form-inline']) !!}
        <div class="form-group">
            <label for="type">ID Ораганизации</label><br>
            <input  class="form-control" type="text" size="50" name="form[organization_id]" value="{{isset($form['organization_id']) ? $form['organization_id'] : ''}}">
        </div>
        <div class="form-group">
            <label for="type">Направление</label><br>
            <select name="form[direction]" class="form-control">
                <option value="0" @if (isset($form['direction']) && $form['direction'] == '0') selected @endif>Все</option>
                <option value="1" @if (isset($form['direction']) && $form['direction'] == '1') selected @endif>Пополнение</option>
                <option value="2" @if (isset($form['direction']) && $form['direction'] == '2') selected @endif>Списание</option>
            </select>
        </div>
        <div class="form-group">
            <label for="type">Назначение платежа</label><br>
        {!!  Form::select('form[use]', $products, isset($form['use']) ? $form['use'] : '', ['class' => 'form-control'])  !!}
        </div>
        <div class="form-group">
            <label for="type">Платежная система</label><br>
            {!!  Form::select('form[ps]', $ps, isset($form['ps']) ? $form['ps'] : '', ['class' => 'form-control'])  !!}
        </div>
        <div class="form-group">
            <label>Дата проведения</label><br>
            <div class='input-group date'>
                <input class="form-control" placeholder="С дд/мм/гггг" value="{{isset($form['start_date']) ? $form['start_date'] : ''}}" date-picker="date-picker" pattern="\d{2}\.\d{2}\.\d{4}" name="form[start_date]" type="text"><span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
            </div>

            <div class='input-group date'>
                <input class="form-control" placeholder="По дд/мм/гггг"  value="{{isset($form['end_date']) ? $form['end_date'] : ''}}" date-picker="date-picker" pattern="\d{2}\.\d{2}\.\d{4}" name="form[end_date]" type="text"><span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
            </div>
        </div>

        <div></div>
        <div class="form-group">
            <label>&nbsp;</label><br>
            <button type="submit" class="btn btn-primary">{{Lang::get('keys.search')}}</button>
        </div>
        {!! Form::close() !!}
    </div>

    @if($payments->count())
        <div class="wrapper">
            <div class="">
                <p>Найдено: {{$payments->total()}}</p>
                <p style="color: green;">Общая сумма пополнения: {{number_format($addSum / 100, 2, '.', ' ')}}</p>
                <p style="color: red;">Общая сумма списания: {{number_format($minusSum / 100, 2, '.', ' ')}}</p>
                <table  class="table table-striped table-bordered sortable tablesorter">
                    <thead>
                    <tr>
                        <th>ID транзакции</th>
                        <th>ID организации</th>
                        <th>Название организации</th>
                        <th>Сумма</th>
                        <th>Остаток</th>
                        <th>Платежная система</th>
                        <th>Назначение платежа</th>
                        <th>Підстава</th>
                        <th>Дата</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($payments as $payment)
                        <tr>
                            <td>{{$payment->id}}</td>
                            <td>{{$payment->user->organization->id}}</td>
                            <td>{{$payment->user->organization->name}}</td>
                            <td style="text-align: right; @if($payment->amount > 0) color:green; @else color:red; @endif">{{$payment->amount > 0 ? '+'.$payment->amount : $payment->amount}}</td>
                            <td style="text-align: right">{{$payment->balance}}</td>
                            <td>
                                @if ($payment->service)
                                    {{$payment->service->name}}
                                @endif
                                @if ($payment->payment_id > 0)
                                    Оплата за участь <a href="{{route('tender.show', [$payment->payment->tender->id])}}">{{$payment->payment->title}}</a>
                                @endif
                            </td>
                            <td>
                                @if ($payment->products_id > 0)
                                    @if(isset($payment->product->name))
                                        {{$payment->product->name}}
                                        @endif

                                @endif
                            <td>{{$payment->comment}}</td>
                            <td>{{$payment->created_at}}</td>
                            <td>
                                @if ($allowEdit)
                                <a href="{{route('admin::payments.edit', ['id' => $payment->id])}}"
                                   class="btn btn-xs btn-info" title-data="Редагування"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></a>
                                    <a onclick="return confirm('Точно?')" href="{{route('admin::payments.delete', ['id' => $payment->id])}}"
                                       class="btn btn-xs btn-danger"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></a>
                                @endif
                                </td>
                        </tr>
                    @endforeach
                    </tbody>

                </table>

            </div>

            <div class="text-center">
                {!! $payments->appends(['form' => $form])->render() !!}
            </div>
        </div>

    @else
        <div class="text-center">За Вашим запитом нічого не знайдено</div>
    @endif

@endsection



