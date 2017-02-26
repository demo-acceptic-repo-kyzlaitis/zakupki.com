@extends('layouts.admin')

@section('content')
    <div class="well">
        {!! Form::open(['url' => route('admin::bids.index'),
                'method'=>'GET',
                'class'=>'form-inline']) !!}
        <div class="form-group">
            <label for="mode">Режим</label><br>
            <select name="form[mode]" class="form-control" onchange="this.form.submit()">
                <option value="" @if ($form['mode'] == '') selected @endif>Всі</option>
                <option value="0" @if ($form['mode'] == '0') selected @endif>Тестовий</option>
                <option value="1" @if ($form['mode'] == '1') selected @endif>Реальний</option>
            </select>
        </div>
        <div class="form-group">
            <label for="mode">Назва компанії</label><br>
            {!!Form::text('form[name]',$form['name'], ['class' => 'form-control'])!!}
       </div>
        <div class="form-group">
            <label for="mode">Id організації</label><br>
            {!!Form::text('form[organization_id]',$form['organization_id'], ['class' => 'form-control'])!!}
        </div>

        <div class="form-group">
            <label for="mode">Тип</label><br>
            <select name="form[type]" class="form-control">
                <option value=""> </option>
                <option value="2" @if($form['type']==2) selected @endif> Лот</option>
                <option value="1" @if($form['type']==1) selected @endif> Тендер</option>
            </select>
        </div>
        <br>
        <br>
        <div class="form-group">
            <label for="mode">Статус тенедру</label><br>
            <select name="form[status]" class="form-control">
                <option></option>
                @foreach($statuses as $status)
                    <option @if ($status->status == $form['status']) selected @endif value="{{$status->status}}">{{$status->description}}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="mode">Id тендеру</label><br>
            {!!Form::text('form[tender_id]',$form['tender_id'], ['class' => 'form-control'])!!}
        </div>
    <div></div>
    <div class="form-group">
        <label>&nbsp;</label><br>
        <button type="submit" class="btn btn-primary">{{Lang::get('keys.search')}}</button>
    </div>
    {!! Form::close() !!}
    </div>
    @if($bids->count())
        <form>
        <div class="">
            <p>Знайдено: {{$bids->total()}}</p>
            <table  class="table table-striped table-bordered">
                <tr>

                    <th>Режим роботи</th>
                    <th>Назва компанії</th>
                    <th>Id організації</th>
                    <th>Назва лоту</th>
                    <th>Тип</th>
                    <th>Id тендеру</th>
                    <th>Статус тенедеру</th>
                    <th>Сума пропозиції</th>
                    <th>Вартість подачі пропозиції</th>
                    <th>Статус пропозиції</th>
                    <th>Документи</th>
                    <th>ЦБД ID</th>
                    <th><a href="{{route('admin::bids.index', ['status' => $searchStatus, 'so' => $sortOrder, 'sf' => 'updated_at'])}}">Дата змін @if ($sortOrder == 'asc') &#9652; @else &#9662;  @endif</a></th>
                </tr>
                @foreach($bids as $bid)

                    <?php
                        if($bid->bidable_id != 0 || isset($bid->organization) && $bid->organization !=0 ||$bid->tender_id !=0){ ?>

                    <tr>
                        <td>{{$bid->bidable->tender->getMode()}}</td>
                        <td>@if (isset($bid->organization->name)){{$bid->organization->name}}  @endif</td>
                        <td>@if (isset($bid->organization->id)){{$bid->organization->id}}  @endif</td>

                        <td>@if (isset($bid->bidable->title)){{$bid->bidable->title}}  @endif</td>
                        <td>{{$bid->getStatus()}}</td>
                        <td>{{$bid->bidable->tender->id}}</td>
                        <td><span class="label label-{{$bid->bidable->tender->statusDesc->style}}">{{$bid->bidable->tender->statusDesc->description}}</span></td>
                        <td>{{$bid->amount}}</td>
                        <td>{{$bid->payment_amount}}</td>
                        <td>{{$bid->status}}
                        <td><a href="{{route('admin::bids.edit', [$bid->id])}}" class="btn btn-xs btn-info"><span class="glyphicon" aria-hidden="true"></span>{{Lang::get('keys.link')}}</a></td>
                        <td>{{$bid->cbd_id}}</td>
                        <td>{{$bid->updated_at}}</td>
                    </tr>
                        <?php } ?>

                @endforeach
            </table>
        </div>
        </form>

        <div class="text-center">
            {!! $bids->appends(['status' => $searchStatus, 'so' => $sortOrder, 'sf' => $sortField,'form'=>$form])->render() !!}
        </div>
    @endif
@endsection



