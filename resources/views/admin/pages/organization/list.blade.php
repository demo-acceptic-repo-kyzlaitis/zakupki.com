@extends('layouts.admin')

@section('content')

    <div class="well">
        {!! Form::open(['url' => route('admin::organization.index'),
                'method'=>'GET',
                'class'=>'form-inline', 'id'=>'search-org-form']) !!}
        <div class="form-group">
            <label for="type">Тип</label><br>
            <select name="form[type]" class="form-control" onchange="this.form.submit()">
                <option value="" @if ($form['type'] == '') selected @endif>Всі</option>
                <option value="customer" @if ($form['type'] == 'customer') selected @endif>Замовник</option>
                <option value="supplier" @if ($form['type'] == 'supplier') selected @endif>Учасник</option>
            </select>
        </div>

        <div class="form-group">
            <label for="mode">Режим</label><br>
            <select name="form[mode]" class="form-control" onchange="this.form.submit()">
                <option value="" @if ($form['mode'] == '') selected @endif>Всі</option>
                <option value="0" @if ($form['mode'] == '0') selected @endif>Тестовий</option>
                <option value="1" @if ($form['mode'] == '1') selected @endif>Реальний</option>
            </select>
        </div>
        <div class="form-group">
            <label>Реєстрація</label><br>

            <div class='input-group date'>
                <input class="form-control" placeholder="З дд/мм/гггг" value="{{$form['start_date']}}"
                       date-picker="date-picker" pattern="\d{2}\.\d{2}\.\d{4}" name="form[start_date]" type="text"><span
                        class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
            </div>

            <div class='input-group date'>
                <input class="form-control" placeholder="По дд/мм/гггг" value="{{$form['end_date']}}"
                       date-picker="date-picker" pattern="\d{2}\.\d{2}\.\d{4}" name="form[end_date]" type="text"><span
                        class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
            </div>
        </div>
        <div>&nbsp;</div>
        <div class="form-group">
            <label for="type">ID</label><br>
            <input  class="form-control" type="text" size="7" name="form[id]" value="{{$form['id']}}">
        </div>
        <div class="form-group">
            <label for="type">Назва</label><br>
            <input class="form-control" type="text" size="50" name="form[name]" value="{{$form['name']}}">
        </div>
        <div class="form-group">
            <label for="type">ЕДРПОУ</label><br>
            <input class="form-control" type="text" name="form[code]" value="{{$form['code']}}">
        </div>
        <div class="form-group">
            <label for="type">Email</label><br>
            <input class="form-control" type="text" name="form[email]" value="{{$form['email']}}">
        </div>
        <div class="form-group">
            <label for="type">Телефон або ім'я</label><br>
            <input class="form-control" type="text" name="form[phone]" value="{{$form['phone']}}">
        </div>
        <div class="form-group">
            <label for="type">Площадка</label><br>
            <select name="form[source]" class="form-control" id="source">
                <option value=""></option>
                <option value="zakupki_0">Закупки</option>
                <option value="zakupki_2">Парус</option>
            </select>
        </div>
        <div></div>
        <div class="form-group">
            <label>&nbsp;</label><br>
            <button type="submit" class="btn btn-primary">{{Lang::get('keys.search')}}</button>
        </div>
        {!! Form::close() !!}
    </div>

    @if($organizations->count())
        <div class="wrapper">
            <div class="">
                <p>Знайдено: {{$organizations->total()}}</p>
                <table class="table table-striped table-bordered sortable tablesorter table-notes">
                    <thead>
                    <tr>
                        <th>Id</th>
                        <th>Користувач</th>
                        <th>Назва</th>
                        <th>Код</th>
                        <th>Баланс</th>
                        <th>Режим</th>
                        <th>Роль</th>
                        <th>К-ть тендерів</th>
                        <th>Дата</th>
                        <th>Площадка</th>
                        <th>Дії</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($organizations as $organization)
                        @if($organization->user)

                        <tr @if ($organization->confirmed == 0) class="danger" @endif>
                            <td>{{$organization->id}}</td>
                            <td>
                                @if(isset($organization->user->id) && isset($organization->user->email))
                                <a href="{{route('admin::user.edit', $organization->user->id)}}">{{$organization->user->email}}</a>
                                    @endif
                            </td>
                            <td>{{$organization->name}}</td>
                            <td>{{$organization->identifier}}</td>
                            <td>
                                @if(isset(\App\Payments\Payments::balance($organization->user->id)->amount))
                                    {{\App\Payments\Payments::balance($organization->user->id)->amount}}
                                @endif</td>
                            <td>{{$organization->mode == 0 ? 'Тестовий' : 'Реальний'}}</td>
                            <td>@if(isset($organization->type)){{$organization->type}}@endif</td>
                            <td>
                                <a href="{{route('admin::tender.index', ['org_id' => $organization->id])}}">{{$organization->tenders->count()}}</a>
                            </td>
                            <td>{{$organization->created_at}}</td>
                            <th>@if($organization->source == 2) Парус @elseif($organization->source == 0) Закупки @else Прозоро @endif</th>
                            <td>
                                @if($organization->source == 2)
                                    <a href="{{route('admin::organization.checksign', $organization->id)}}" class="btn btn-xs btn-info helper" title-data="Перевірка підпису">
                                        <span class="glyphicon glyphicon-lock"></span>
                                    </a>

                                    <a href="http://lapo.it/asn1js/#{{$organization->sign}}" class="btn btn-xs btn-info clipboard" id="copyButton" title-data="Ручна перевірка">
                                        <span class="glyphicon glyphicon-eye-open"></span>
                                    </a>

                                    <input type="text" name="Element To Be Copied" id="inputContainingTextToBeCopied" value="{{$organization->identifier}}" style="display:none; position: relative; left: -10000px;"/>
                                @endif
                                <a href="{{route('admin::organization.edit', $organization->id)}}"
                                   class="btn btn-xs btn-info helper" title-data="Редагування"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></a>
                                <?php /*if ($organization->confirmed == 0) <a href="{{route('admin::organization.confirm', $organization->id)}}" class="btn btn-xs btn-warning"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span></a> endif */?>
                                <a href="{{route('admin::paysystem.manualPay', $organization->user->id)}}"
                                   class="btn btn-xs btn-info">
                                    <span class="glyphicon" aria-hidden="true">$</span>
                                </a>
                                @if ($organization->confirmed == 0)
                                    <a href="{{route('admin::organization.confirm', $organization->id)}}"
                                       class="btn btn-xs btn-warning helper" title-data="Ok">
                                        <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
                                    </a>
                                @else
                                    <a href="{{route('admin::organization.confirm', $organization->id)}}"
                                       class="btn btn-xs btn-danger">
                                        <span class="glyphicon glyphicon-minus" aria-hidden="true"></span>
                                    </a>
                                @endif

                                </td>
                            </tr>

                        @endif
                    @endforeach
                    </tbody>

                </table>

            </div>

            <div class="text-center">
                {!! $organizations->appends(['form' => $form])->render() !!}
            </div>
        </div>

    @else
        <div class="text-center">За Вашим запитом нічого не знайдено</div>
    @endif
@endsection



