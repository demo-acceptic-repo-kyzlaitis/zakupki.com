@extends('layouts.index')

@section('content')
    {{--Editing Section Start--}}
    <section class="registration container">
        <h2>Редагування закупівлі</h2>
        <div class="row">
            <div class="col-md-1"></div>
            <div class="col-md-9">
                @if($errors->has())
                    <div class="alert alert-danger" role="alert">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                    @if($tender->errors->count())
                        <div class="alert alert-danger" role="alert">
                            <ul>
                                @foreach ($tender->errors as $error)
                                    <li>{{ $error->description->text }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                {!! Form::model($tender,['route' => ['tender.update', $tender->id],
                'method'=>'PUT',
                'enctype'=>'multipart/form-data',
                'class'=>'form-horizontal',]) !!}

                    @include('pages.tender.'.$template.'below')

                    <hr>
                    <div class="form-group">
                        <div class="col-lg-12 text-center">
                            {!! Form::submit(Lang::get('keys.save'),['class'=>'btn btn-lg btn-danger']) !!}
                        </div>
                    </div>
                </fieldset>
                {!! Form::close() !!}
            </div>
            <div class="col-md-1"></div>
        </div>
    </section>
    {{--Editing Section End--}}
@endsection