@extends('layouts.home')
@section('content')
    <div class="bg" style="background: url(/i/under1.jpg) no-repeat 50% 0; height: 100vh; background-position: center top;
    background-size: 100% auto" >
    <div class="container">
        <div class="row">
            <div class="col-md-3"></div>
            <div class="col-md-6">
                <div class="header-form">
                    <img src="/i/logo.png">
                </div>
                <div class="form-container">
                    {!! Form::open(['route'=>'user.login','method'=>'POST']) !!}

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input class="form-control" placeholder="email" type="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Пароль</label>
                            <input class="form-control" placeholder="password" type="password" name="password" required>
                        </div>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox"> Запам'ятати мене
                            </label>
                        </div>
                        <br>
                        <button type="submit" class="btn btn-danger">{{Lang::get('keys.login')}}</button>
                        &nbsp;&nbsp;или&nbsp;&nbsp;
                        <a href="/register" style="color: red;">{{Lang::get('keys.register')}}</a>
                        <br><br><br>
                        <a href="#">Забули пароль?</a>
                    {!! Form::close() !!}
                </div>

            </div>
            <div class="col-md-3"></div>
        </div>


    </div>
    </div>
@endsection