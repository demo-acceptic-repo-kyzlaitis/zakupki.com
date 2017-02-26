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
            @include('share.component.buttons')
            @include('share.component.tabs')
        </div>
    </div>

    <h3>{{$terminateType == 'success' ? 'Звіт про виконання договору' : 'Розірвати договір'}}</h3>
    <br>
    @include('pages.contract.component.terminate-form')
</div>

@endsection