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
                @include('pages.cancellation.component.cancel-detail')
            </div>
        </div>
        {{--Editing Section End--}}
    </div>
@endsection