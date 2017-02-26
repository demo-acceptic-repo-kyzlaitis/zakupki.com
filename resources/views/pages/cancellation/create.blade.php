@extends('layouts.index')

@section('content')
    {{--Editing Section Start--}}
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                @include('share.component.title')
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                @include('share.component.tabs')
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <section class="registration container">
                    <h4>Заявка на відміну закупівлі</h4>
                    @if($errors->has())
                        <div class="alert alert-danger" role="alert">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    {!! Form::open(['url' => "/cancellation",
                    'method'=>'POST',
                    'enctype'=>'multipart/form-data',
                    'class'=>'form-horizontal',]) !!}
                    <fieldset>
                        @include('pages.cancellation.component.cancel-form')
                        <hr>
                        <div class="form-group">
                            <div class="col-lg-12 text-center">
                                {!! Form::submit(Lang::get('keys.create'),['class'=>'btn btn-danger']) !!}
                            </div>
                        </div>
                    </fieldset>
                    {!! Form::close() !!}
                </section>
                {{--Editing Section End--}}
            </div>
        </div>
    </div>
@endsection