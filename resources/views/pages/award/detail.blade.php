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
                        <p><br></p>
                            @include('pages.award.component.detail')
                    </section>
            </div>
        </div>
        {{--Editing Section End--}}
    </div>
@endsection