@extends('layouts.admin')

@section('content')
    <div class="well">
        {!! Form::open(['url' => route('admin::tender.index'),
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
        <div></div>
        <div class="form-group">
            <label>&nbsp;</label><br>
            <button type="submit" class="btn btn-primary">{{Lang::get('keys.search')}}</button>
        </div>
        {!! Form::close() !!}
    </div>
    @if($tenders->count())
        <form>
            @if (isset($organizationId))
                <input type="hidden" name="orig_id" value="{{$organizationId}}">
            @endif
        <div class="">
            <p>Знайдено: {{$tenders->total()}}</p>
            <table  class="table table-striped table-bordered">
                <tr>
                    <th>Id</th>
                    <th><a href="{{route('admin::tender.index', ['status' => $searchStatus, 'so' => $sortOrder, 'sf' => 'tenderID'])}}">UAID @if ($sortOrder == 'asc') &#9652; @else &#9662; @endif</a></th>
                    <th>Найменування</th>
                    <th>
                        <select name="status" class="form-control" onchange="this.form.submit()">
                            <option></option>
                            @foreach($statuses as $status)
                                <option @if ($status->status == $searchStatus) selected @endif value="{{$status->status}}">{{$status->description}}</option>
                            @endforeach
                        </select>
                    </th>
                    <th><a href="{{route('admin::tender.index', ['status' => $searchStatus, 'so' => $sortOrder, 'sf' => 'created_at'])}}">Дата змін @if ($sortOrder == 'asc') &#9652; @else &#9662;  @endif</a></th>
                    <th>Дії</th>
                </tr>
                @foreach($tenders as $tender)

                    <tr>
                        <td>{{$tender->id}}</td>
                        <td>@if (empty($tender->tenderID)) -- @else <a href="https://lb.api-sandbox.openprocurement.org/api/0.8/tenders/{{$tender->cbd_id}}">{{$tender->tenderID}}</a>@endif</td>
                        <td><a href="{{route('tender.show', [$tender->id])}}">{{$tender->title}}</a></td>
                        <td><span class="label label-{{$tender->statusDesc->style}}">{{$tender->statusDesc->description}}</span></td>
                        <td>{{$tender->date_modified}}</td>
                        <td>
                            @if ($tender->canEdit())
                                <a href="{{route('admin::tender.edit', [$tender->id])}}" class="btn btn-xs btn-info helper" title-data="Редагування"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></a>
                            @endif
                            @if ($tender->canPublish())
                                <a href="{{route('admin::tender.publish', [$tender->id])}}" class="btn btn-xs btn-info"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span></a>
                            @endif
                        </td>
                    </tr>

                @endforeach
            </table>
        </div>
        </form>

        <div class="text-center">
            {!! $tenders->appends(['status' => $searchStatus, 'so' => $sortOrder, 'sf' => $sortField])->render() !!}
        </div>
    @endif
@endsection



