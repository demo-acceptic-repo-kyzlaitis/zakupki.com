@extends('layouts.admin')

@section('content')

    <div class="well">
        {!! Form::open(['url' => route('admin::stats.organizations'),
                'method'=>'GET',
                'class'=>'form-inline', 'id'=>'search-org-form']) !!}


        <div class="form-group">
            <label for="mode">Режим</label><br>
            <select name="form[mode]" class="form-control">
                <option value="" @if ($form['mode'] == '') selected @endif>Всі</option>
                <option value="0" @if ($form['mode'] == '0') selected @endif>Тестовий</option>
                <option value="1" @if ($form['mode'] == '1') selected @endif>Реальний</option>
            </select>
        </div>
        {{--<div class="form-group">--}}
            {{--<label>ID тендера</label><br>--}}

            {{--<input class="form-control" type="text" name="form[tender_id]" value="{{$form['tender_id']}}">--}}
        {{--</div>--}}
        <div class="form-group">
            <label for="type">Телефон</label><br>
            <input class="form-control" type="tel" name="form[phone]" value="{{$form['phone']}}">
        </div>
        <div class="form-group">
            <label for="type">Имя участника</label><br>
            <input class="form-control" type="text" name="form[name]" value="{{$form['name']}}">
        </div>
        <div class="form-group">
            <label for="type">Email</label><br>
            <input class="form-control" type="text" name="form[email]" value="{{$form['email']}}">
        </div>
        <div class="form-group">
            <label>ID ЭДЕРПОУ</label><br>
            <input class="form-control" type="text" name="form[classifier]" value="{{$form['classifier']}}">
        </div>
        <div class="form-group">
            <label for="type">Название организации</label><br>
            <input class="form-control" type="text" name="form[legalName]" value="{{$form['legalName']}}">
        </div>
        <div class="form-group">
            <label for="type">Индекс</label><br>
            <input class="form-control" type="text" name="form[postalCode]" value="{{$form['postalCode']}}">
        </div>
        <div class="form-group">
            <label for="type">Страна</label><br>
            <select name="form[country]" class="form-control" id="region">
                <option value=""></option>
                @foreach($countries as $countryId => $country)
                    <option value="{{$countryId}}" @if ($countryId == $form['country']) selected @endif>{{$country}}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="type">Адрес</label><br>
            <input class="form-control" type="text" name="form[street]" value="{{$form['street']}}">
        </div>
        <div class="form-group">
            <label for="type">Регион</label><br>
            <select name="form[region]" class="form-control" id="region">
                <option value=""></option>
                @foreach($regions as $regionId => $region)
                    <option value="{{$regionId}}" @if ($regionId == $form['region']) selected @endif>{{$region}}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label>Дата добавления</label><br>

            <div class='input-group date'>
                <input class="form-control" placeholder="З дд/мм/гггг" value="{{$form['start_add_date']}}"
                       date-picker="date-picker" pattern="\d{2}\.\d{2}\.\d{4}" name="form[start_add_date]" type="text"><span
                        class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
            </div>

            <div class='input-group date'>
                <input class="form-control" placeholder="По дд/мм/гггг" value="{{$form['end_add_date']}}"
                       date-picker="date-picker" pattern="\d{2}\.\d{2}\.\d{4}" name="form[end_add_date]" type="text"><span
                        class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
            </div>
        </div>
        {{--<div class="form-group">
            <label>Количество пропозиций в 1 м тендере</label><br>

            <div class='input-group'>
                <input placeholder="От" class="form-control" type="text" name="form[bid_count_from]" value="{{$form['bid_count_from']}}">
            </div>

            <div class='input-group'>
                <input placeholder="До" class="form-control" type="text" name="form[bid_count_to]" value="{{$form['bid_count_to']}}">
            </div>
        </div>--}}
        <div></div>
        <div class="form-group">
            <label>&nbsp;</label><br>
            <button type="submit" class="btn btn-primary">{{Lang::get('keys.search')}}</button>
        </div>
        {!! Form::close() !!}
    </div>

    @if($organizations->count())
        <div>
            <div>
                <p>Знайдено: {{$organizations->total()}}</p>
                <table class="table table-striped table-bordered sortable tablesorter table-notes">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Режим</th>
                        <th>Email</th>
                        <th>Имя пользователя</th>
                        <th>Телефон</th>
                        <th>Имя организации</th>
                        <th>Схема классификатора</th>
                        <th>ID классификатора</th>
                        <th>Страна</th>
                        <th>Регион</th>
                        <th>Адресс</th>
                        <th>Почтовый номер</th>
                        <th>Дата добавления</th>
                        <th>Предложения</th>
                        <th>Намерения укласть договор</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($organizations as $organization)

                        <tr>
                            <td>{{$organization->id}}</td>
                            <td>{{($organization->mode == 0) ? 'Тестовый' : 'Реальный'}}</td>
                            <td>{{$organization->contact_email}}</td>
                            <td>{{$organization->contact_name}}</td>
                            <td>{{$organization->contact_phone}}</td>
                            <td>{{$organization->name}}</td>
                            <td>{{(isset($organization->identifiersScheme[0])) ? $organization->identifiersScheme[0]->scheme : ''}}</td>
                            <td>{{$organization->identifier}}</td>
                            <td>{{(isset($countries[$organization->country_id])) ? $countries[$organization->country_id] : ''}}</td>
                            <td>{{(isset($regions[$organization->region_id])) ? $regions[$organization->region_id] : ''}}</td>
                            <td>{{$organization->street_address}}</td>
                            <td>{{$organization->postal_code}}</td>
                            <td>{{$organization->created_at}}</td>

                            <td>
                                Всего - {{$organization->bids->count()}}<br>
                                Активные - {{$organization->bids->where('status', 'active')->count()}}<br>
                                В ожидании - {{$organization->bids->where('status', 'pending')->count()}}
                                <hr>
                                @foreach($organization->bids as $bid)
                                    <a href="{{route('tender.show', [$bid->tender_id])}}">{{$bid->tender->tenderID}}</a> - {{$bid->getAmountAttribute($bid->amount)}}<br>
                                @endforeach
                            </td>
                            <td>
                                Всего - {{$organization->awards->count()}}<br>
                                Победитель - {{$organization->awards->where('status', 'active')->count()}}<br>
                                Рассматривается - {{$organization->awards->where('status', 'pending')->count()}}<br>
                                Отклонено - {{$organization->awards->where('status', 'unsuccessful')->count()}}<br>
                                Отменено - {{$organization->awards->where('status', 'cancelled')->count()}}<br>
                                В процесе активации - {{$organization->awards->where('status', 'activate')->count()}}
                                <hr>
                                @foreach($organization->awards as $award)
                                    <a href="{{route('tender.show', [$award->tender_id])}}">{{$award->tender->tenderID}}</a> - {{$award->getAmountAttribute($award->amount)}}<br>
                                @endforeach
                            </td>
                        </tr>

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



