@extends('layouts.admin')

@section('content')


    <h2>Список транзакций</h2>
    <div class="wrapper">
        <div class="">
            <p>Записей: {{count($data)}}</p>
            <form method="post" action="{{route('admin::payments.commit')}}">
                {{csrf_field()}}
            <table  class="table table-striped table-bordered sortable tablesorter">
                <tr>
                    <th>ЕДРПОУ</th>
                    <th>ID организации</th>
                    <th>Назва</th>
                    <th>Рахунок</th>
                    <th>Сумма</th>
                    <th>Призначення</th>
                    <td>Примітки</td>
                </tr>
                @foreach($data as $index => $line)
                    @if (isset($line[0]))
                    <tr @if (!$line['transaction'] && $line['organization']) class="success" @elseif($line['transaction'] && $line['organization']) class="danger" @endif>
                        <td>{{$line[9]}}</td>
                        <td>@if ($line['organization'])
                                <a target="_blank" href="{{route('admin::payments.index',
                                    ['form[organization_id]' =>$line['organization']->id, 'form[code]' => ''])}}">
                                    {{$line['organization']->id}}
                                </a> @else Не знайдено @endif</td>
                        <td>@if ($line['organization'])
                                <a target="_blank" href="{{route('admin::organization.edit', [$line['organization']->id])}}">
                                    {{$line['organization']->name}}
                                </a> @else Не знайдено @endif</td>
                        <td>@if ($line['order']) {{$line['order']->number}}
                                @if ($line['order']->status == 'payed') <span class="text-danger">Вже оплачений!</span> @endif
                            @endif
                        </td>
                        <td>{{$line[14]}}</td>
                        <td>{{iconv('Windows-1251', 'utf-8', $line[15])}}</td>
                        <td>@if ($line['transaction']) Така транзакція №{{$line['transaction']->id}} вже існує@endif</td>
                    </tr>
                    @endif
                    @if (!$line['transaction'] && $line['organization'])
                        <input type="hidden" name="line[{{$index}}][organization_id]" value="{{$line['organization']->id}}">
                        <input type="hidden" name="line[{{$index}}][amount]" value="{{$line[14]}}">
                        <input type="hidden" name="line[{{$index}}][comment]" value="{{iconv('Windows-1251', 'utf-8', $line[15])}}">
                        <input type="hidden" name="line[{{$index}}][date]" value="{{$line['date_transaction']}}">
                        @if ($line['order'])
                            <input type="hidden" name="line[{{$index}}][order_id]" value="{{$line['order']->id}}">
                        @endif
                    @endif
                @endforeach
            </table>
                <p class="text-center"><button type="submit" class="btn btn-success">{{Lang::get('keys.download')}}</button> </p>
            </form>
        </div>
    </div>

@endsection



