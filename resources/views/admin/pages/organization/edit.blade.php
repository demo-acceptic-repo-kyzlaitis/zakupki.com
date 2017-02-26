@extends('layouts.admin')

@section('content')
    {{--Editing Section Start--}}
    <section class="registration container">
        <div class="well">
            <legend>Редагування даних орагнізації</legend>
        {!! Form::model($organization, ['route' => ['admin::organization.update', $organization->id], 'method'=>'PUT', 'class'=>'form-horizontal',]) !!}
        @if($errors->has())
            <div class="alert alert-danger" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <fieldset>

            @include('pages.organization.component.form')

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