@extends('layouts.index')

@section('content')
    <section class="registration container">
        <h2>Email не підтверджено</h2>
        <p>
            Вам було відправлено лист з посиланням для підтвердження, будь ласка, перейдіть за посиланням. Якщо Ви не отримали лист, натисніть "Відправити знову".
        </p>
        <form method="post" action="{{route('user.resend')}}">
            {{ csrf_field() }}
            <input type="hidden" name="id" value="{{$user->id}}">
            <button type="submit" class="btn btn-primary">{{Lang::get('keys.send_again')}}</button>
        </form>

    </section>
@endsection