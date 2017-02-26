@extends('layouts.index')

@section('content')
    <section class="registration container">
        <h2>Реєстрацію завершено!</h2>
        <script type="text/javascript">ga('send', 'pageview', '/reg');</script>
        <p> 

            На Ваш e-mail був відправлений лист з посиланням для активації аккаунта, будь ласка, перейдіть за посиланням. Якщо Ви не отримали лист, запросіть повторну відправку, натиснувши на кнопку нижче. 
        </p>
        <form method="post" action="{{route('user.resend')}}">
            {{ csrf_field() }}
            <input type="hidden" name="id" value="{{$user->id}}">
            <button type="submit" class="btn btn-primary">{{Lang::get('keys.send_again')}}</button>
        </form>

    </section>
@endsection