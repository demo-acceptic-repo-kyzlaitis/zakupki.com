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
                        {!! Form::open(['route'=>'password.reset','method'=>'POST']) !!}
                        @if (count($errors) > 0)
                            <div class="text-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            </div>
                        @endif
                        <input type="hidden" name="token" value="{{ $token }}">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input class="form-control" placeholder="email" value="{{old('email')}}" type="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Пароль</label>
                            <input class="form-control" placeholder="" type="password" name="password" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Підтвердження паролю</label>
                            <input class="form-control" placeholder="" type="password" name="password_confirmation" required>
                        </div>
                        <br>
                        <button type="submit" class="btn btn-danger">{{Lang::get('keys.save')}}</button>
                        {!! Form::close() !!}
                    </div>

                </div>
                <div class="col-md-3"></div>
            </div>


        </div>
    </div>
@endsection
