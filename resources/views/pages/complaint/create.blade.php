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
                    @if($errors->has())
                        <div class="alert alert-danger" role="alert">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    {!! Form::open(['url' => "/complaint",
                    'method'=>'POST',
                    'enctype'=>'multipart/form-data',
                    'class'=>'form-horizontal',]) !!}
                    <fieldset>
                        @include('pages.complaint.component.draft')
                    </fieldset>
                    {!! Form::close() !!}
                </section>
                {{--Editing Section End--}}
            </div>
        </div>
    </div>
@endsection