<p>Ваша адреса електронної пошти <b> ({{$user->email}}) </b> була вказана при реєстрації на сайті <b>{{env('BASE_URL')}}</b>.</p>
<p>Код підтвердження реєстрації: {{$user->activation_code}} </p>
<p>Для підтвердження реєстрації перейдіть, будь ласка, за посиланням:<br>
<a href="{{env('BASE_URL')}}{{route('user.activate', $user->activation_code, false)}}">{{env('BASE_URL')}}{{route('user.activate', $user->activation_code, false)}}</a></p>
<p>Якщо Ви не реєструвались, просто проігноруйте цей лист.</p>

@include('emails.footer-email-sign')