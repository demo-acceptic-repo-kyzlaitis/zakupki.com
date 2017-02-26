@extends('layouts.index')

@section('content')
    <div class="container">
        <h2 class="text-center">
            Міграція закупівлі
        </h2>

        @if($errors->has())
            <div class="alert alert-danger" role="alert" >
                <ul id="errors">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        {!! Form::open(['url' => route('tender.transfer.store'), 'class' => 'form-horizontal']) !!}

            <div class="form-group">
                <label for="title" class="col-md-2 control-label">
                    Ключ міграції
                </label>
                <div class="col-md-8">
                    {!! Form::text('transfer', null, ['class' => 'form-control', 'placeholder' =>'', 'required'])  !!}
                </div>
            </div>
            <div class="form-group">
                <label for="title" class="col-md-2 control-label">
                    Номер тендера
                </label>
                <div class="col-md-8">
                    {!! Form::text('tenderID', null, ['class' => 'form-control', 'placeholder' =>'', 'required'])  !!}
                </div>
            </div>

            {!! Form::submit(Lang::get('keys.move_tender'), ['class' => 'btn btn-success pull-right']) !!}
        {!! Form::close() !!}
    </div>

@endsection