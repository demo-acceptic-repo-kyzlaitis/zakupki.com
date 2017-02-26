@extends('layouts.admin')

@section('content')

    <div class="well">
        {!! Form::open(['url' => route('admin::complaint.excel'),
                'method'=>'GET',
                'class'=>'form-inline', 'id'=>'search-org-form']) !!}

        <div class="form-group">
            <label>Получение жалоб в период </label><br>

            <div class='input-group date'>
                <input class="form-control" placeholder="З дд/мм/гггг" value=""
                       date-picker="date-picker" pattern="\d{2}\.\d{2}\.\d{4}" name="form[start_date]" type="text"><span
                        class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
            </div>

            <div class='input-group date'>
                <input class="form-control" placeholder="По дд/мм/гггг" value=""
                       date-picker="date-picker" pattern="\d{2}\.\d{2}\.\d{4}" name="form[end_date]" type="text"><span
                        class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
            </div>
        </div>
        <div class="form-group">
            <label>&nbsp;</label><br>
            <button type="submit" class="btn btn-primary">{{Lang::get('keys.receive')}}</button>
        </div>
        {!! Form::close() !!}
    </div>

@endsection



