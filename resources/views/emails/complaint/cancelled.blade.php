<p>Шановний користувач, Ваше звернення щодо закупівлі <b> №{{$tender->id}} </b> було відкликано.<br>
Щоб перевірити його поточний статус, перейдіть за <b><a href="{{env('BASE_URL')}}/complaint/{{$complaint->id}}">посиланням</b>.</a><br>

@include('emails.footer-email-sign')