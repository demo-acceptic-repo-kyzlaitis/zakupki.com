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
                @if ($cancel->status != 'active')
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
                        {!! Form::model($cancel,['route' => ['cancellation.update', $cancel->id],
                        'method'=>'PUT',
                        'enctype'=>'multipart/form-data',
                        'class'=>'form-horizontal',]) !!}
                        <fieldset>
                            @include('pages.cancellation.component.cancel-form')
                            <hr>
                            <div class="form-group">
                                <div class="col-lg-12 text-center">

                                    {!! Form::submit(Lang::get('keys.save'),['class'=>'btn btn-danger']) !!}
                                    <a href="{{route('cancel.activate', [$cancel->id])}}" class="btn btn btn-info">{{Lang::get('keys.activate_request')}}</a>

                                </div>
                            </div>
                        </fieldset>
                        {!! Form::close() !!}
                    </section>
                @else
                    @include('pages.cancellation.component.cancel-detail')
                @endif
            </div>
        </div>
        {{--Editing Section End--}}
    </div>
@endsection