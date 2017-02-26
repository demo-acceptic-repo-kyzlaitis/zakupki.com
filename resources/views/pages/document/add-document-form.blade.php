@extends('layouts.index')

@section('content')
    {{--Editing Section Start--}}
    <section class="registration container">

        {!! Form::open(['url' => "/organization/{$organizationWrongId}/tender/{$tenderWrongId}/document",
                        'method'=>'POST',
                        'enctype'=>'multipart/form-data',
                        'class'=>'form-horizontal',]) !!}
        <fieldset>
            <legend>ДОДАТИ ДОКУМЕНТИ ДО ТЕНДЕРУ</legend>

            <div class="form-group">
                <div class="col-md-12">
                    {{$tender->tenderID}}
                </div>
                <div class="col-md-12">
                    {{$tender->title}}
                </div>
                <div class="col-md-12">
                    {{$tender->description}}
                </div>
            </div>

            @include('share.component.add-file-component')

            <div class="form-group">
                <div class="col-lg-12">
                    {{--{!! Form::reset('ОЧИСТИТИ',['class'=>'btn btn-default']) !!}--}}
                    {!! Form::submit(Lang::get('keys.download'),['class'=>'btn btn-primary']) !!}
                </div>
            </div>
        </fieldset>
        {!! Form::close() !!}
        @if($errors->has())
            {{$errors->__toString()}}
        @endif
    </section>
    {{--Editing Section End--}}
@endsection