@extends('layouts.home')
@section('content')
    <div class="bg">
        <div class="container">
            @if(\Illuminate\Support\Facades\Session::has('sessionHasExpired'))
                <div class="modal fade" id="sessionHasExpired">
                	<div class="modal-dialog">
                		<div class="modal-content">
                			<div class="modal-header">
                				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                				<h4 class="modal-title">Сессія була завершена</h4>
                			</div>
                			<div class="modal-body">
                				Сессія була завершена уввійдить знову
                			</div>
                			<div class="modal-footer">
                				<button type="button" class="btn btn-primary" id="sessionHasExpired">{{Lang::get('keys.next')}}</button>
                			</div>
                		</div><!-- /.modal-content -->
                	</div><!-- /.modal-dialog -->
                </div><!-- /.modal -->
                <script>
                    window.onload = function() {
                        $('#sessionHasExpired').modal('show');
                    };
                    $('button#sessionHasExpired').click(function() {
                        $('#sessionHasExpired').modal('hide');
                    })

                </script>

            @endif
            <div class="row">
                <div class="col-md-3"></div>
                <div class="col-md-6 {{$errors->any() ? 'animated shake':''}} header-form-container">
                    <div class="header-form">
                        <img src="/i/logo.png">
                    </div>
                    <div class="form-container">
                        {!! Form::open(['route'=>'user.login','method'=>'POST']) !!}

                        <div class="form-group {{$errors->has('wrongLogin') ? 'has-error':''}}">
                            <label for="email">Email</label>
                            <input class="form-control" placeholder="email" value="{{old('email')}}" type="email" name="email" required>
                        </div>
                        @if($errors->has('wrongLogin'))
                            <p class="text-danger">Неправильний логін або пароль.</p>
                        @endif
                        <div class="form-group {{$errors->has('wrongLogin') ? 'has-error':''}}">
                            <label for="password">Пароль</label>
                            <input class="form-control" placeholder="password" type="password" name="password" required>
                        </div>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="remember" value="1"> Запам'ятати мене
                            </label>
                        </div>
                        <br>
                        <button type="submit" class="btn btn-danger" onclick="ga('send', 'event', 'behavior', 'login');">{{Lang::get('keys.login')}}</button>
                        &nbsp;&nbsp;або&nbsp;&nbsp;
                        <a href="{{route('user.register')}}" style="color: red;">{{Lang::get('keys.register')}}</a>
                        <br><br><br>
                        <a href="{{route('password.email')}}">Забули пароль?</a>
                        {!! Form::close() !!}
                    </div>

                </div>
                <div class="col-md-3"></div>
            </div>


        </div>
    </div>
@endsection