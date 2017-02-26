@extends('layouts.admin')

@section('content')
    {{--Editing Section Start--}}
    <section class="registration container">
        <div class="well">
            <legend>Редагування профілю</legend>
        @if($errors->has())
            <div class="alert alert-danger" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
            {!! Form::model($user,['route' => ['admin::user.update', $user->id],
                'method'=>'PUT',
                'enctype'=>'multipart/form-data',
                'class'=>'form-horizontal',]) !!}
        <fieldset>

            @include('admin.pages.user.component.content-form')

            <hr>
            <div class="form-group">
                <div class="col-lg-12 text-center">
                    {!! Form::submit(Lang::get('keys.save'),['class'=>'btn btn-danger']) !!}
                </div>
            </div>
        </fieldset>
        {!! Form::close() !!}
        </div>
    </section>
    {{--Editing Section End--}}
@endsection