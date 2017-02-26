@extends('layouts.index')

@section('content')
    {{--Editing Section Start--}}
    <section class="registration container">
        {!! Form::open(['url' => '/',
                            'method'=>'POST',
                            'class'=>'form-horizontal',]) !!}
        <fieldset>
            <legend>ФОРМА СТВОРЕННЯ ІДЕНТИФІКАТОРУ ОРГАНІЗАЦІЇ</legend>

            @include('pages.identifier.component.form')

            <div class="form-group">
                <div class="col-lg-12">
                    {!! Form::reset(Lang::get('keys.clear'),['class'=>'btn btn-default']) !!}
                    {!! Form::submit(Lang::get('keys.create'),['class'=>'btn btn-primary']) !!}
                </div>
            </div>
        </fieldset>
        {!! Form::close() !!}
    </section>
    {{--Editing Section End--}}
@endsection