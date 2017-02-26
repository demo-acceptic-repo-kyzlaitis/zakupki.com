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
                    <h4>Інформація про переможця</h4>
                    @if($errors->has())
                        <div class="alert alert-danger" role="alert">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    {!! Form::model($award,['route' => ['award.update', $award->id],
                    'method'=>'PUT',
                    'enctype'=>'multipart/form-data',
                    'class'=>'form-horizontal',]) !!}
                    <fieldset>
                        @include('pages.award.component.form-limited')
                        <hr>
                        <div class="form-group">
                            <div class="col-lg-12 text-center">

                                {!! Form::submit(Lang::get('keys.save'),['class'=>'btn btn-danger']) !!}
                                @if ($tender->type_id == 4)
                                    <a href="{{route('bid.confirm', [$award->id])}}" class="btn btn-xm btn-success"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span>{{Lang::get('keys.winner_confirm')}}</a>
                                @endif
                            </div>
                        </div>
                    </fieldset>
                    {!! Form::close() !!}
                </section>
            </div>
        </div>
        {{--Editing Section End--}}
    </div>
@endsection