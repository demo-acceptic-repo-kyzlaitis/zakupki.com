@extends('layouts.index')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                @include('share.component.title')
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                @include('share.component.buttons')
                @include('share.component.tabs')
            </div>
        </div>
        <div style="background: #eee; padding: 10px; margin-bottom: 15px; line-height: 2.5em;">
            <b>{{$award->bid->bidable->title}}</b></div>
        <div class="row">

            <div class="col-md-12">
                @include('pages.award.component.form', ['index'=>0])
            </div>
        </div>
    </div>
@endsection