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
    @if($errors->has())
        <div class="alert alert-danger" role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @include('pages.contract.component.change-form')
</div>

@endsection