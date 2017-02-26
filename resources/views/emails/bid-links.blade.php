
<h1>Є активні пропозиції без посилання на аукціон.</h1>
<p>Id організації: {{$bid->organization_id}} </p>
<p>Id тендеру: {{$bid->tender_id}} </p>
<p>Тип сутності: {{$bid->bidable_type}} </p>
<p>Id сутності: {{$bid->bidable_id}} </p>
<p>Початок аукціону: {{$bid->tender->auction_start_date}} </p>
<p>Закінчення аукціону: {{$bid->tender->auction_end_date}} </p>
@include('emails.footer-email-sign')