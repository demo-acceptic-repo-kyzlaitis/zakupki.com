@extends('layouts.index')

@section('content')
    {{--Editing Section Start--}}
    <section class="registration container">
        <h2>Профайл</h2>
        @if($errors->has())
            <div class="alert alert-danger" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        {!! Form::model($user,['url' => "/user/update", 'method'=>'PUT', 'class'=>'form-horizontal',]) !!}
        <fieldset>

            @include('pages.user.component.content-form')

            <hr>
            <div class="form-group">
                <div class="col-lg-12">
                    {!! Form::submit(Lang::get('keys.save'),['class'=>'btn btn-danger']) !!}
                </div>
            </div>
        </fieldset>
        {!! Form::close() !!}

    </section>
    {{--Editing Section End--}}
@endsection