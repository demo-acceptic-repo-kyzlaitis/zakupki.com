@extends('layouts.index')

@section('content')
    {{--Registration Section Start--}}
    <section class="registration container">
        <h2>Реєстрація</h2>
        @if($errors->has())
            <div class="alert alert-danger" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        {!! Form::open(['route' => 'user.create',
                        'method'=>'POST',
                        'class'=>'form-horizontal',]) !!}
        <fieldset>

            @include('pages.user.component.content-form')

            <hr>
            <div class="form-group">
                <div class="col-lg-12 text-center">
                    {!! Form::submit(Lang::get('keys.register'),['class'=>'btn btn-danger btn-lg']) !!}
                </div>
            </div>
        </fieldset>

    </section>
    {{--Registration Section End--}}
@endsection