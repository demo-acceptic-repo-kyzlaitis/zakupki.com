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
        <div class="row">
            <div class="col-md-12">
                @include('pages.question.component.questions-list')
            </div>
        </div>
    </div>
@endsection