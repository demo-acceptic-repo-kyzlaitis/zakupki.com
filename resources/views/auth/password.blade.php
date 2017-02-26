@extends('layouts.home')
@section('content')
    <div class="bg">
        <div class="container">
            <div class="row">
                <div class="col-md-3"></div>
                <div class="col-md-6 {{$errors->any() ? 'animated shake':''}} header-form-container">
                    <div class="header-form">
                        <img src="/i/logo.png">
                    </div>
                    <div class="form-container">
                        {!! Form::open(['route'=>'password.email','method'=>'POST']) !!}
                        @if (session('status'))
                            <div class="alert alert-success">
                                {{ session('status') }}
                            </div>
                            <div class="text-center"><a class="btn btn-primary" href="{{route('home')}}" style="text-decoration: none">{{Lang::get('keys.ok')}}</a></div>
                        @else
                        <div class="form-group">
                            @if($errors->any())
                                    @foreach ($errors->all() as $error)
                                    <p class="alert alert-danger">{{ $error }}</p>
                                    @endforeach
                            @endif
                            <label for="email">Для відновлення паролю введіть свій Email</label>
                            <input class="form-control" placeholder="email" value="{{old('email')}}" type="email" name="email" required>
                        </div>
                        <br>
                            <a class="btn btn-primary" href="{{route('home')}}" style="text-decoration: none">{{Lang::get('keys.back')}}</a>
                        <button type="submit" class="btn btn-danger">{{Lang::get('keys.next')}}</button>
                        @endif
                        {!! Form::close() !!}
                    </div>

                </div>
                <div class="col-md-3"></div>
            </div>


        </div>
    </div>
@endsection