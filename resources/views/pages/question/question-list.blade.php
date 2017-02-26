@extends('layouts.index')

@section('content')
<div class="container">
    <div class="row">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Заголовок запитання</th>
                    <th>Запитання</th>
                    <th>Дата і час подачі запитання</th>
                    <th>Відповідь</th>
                    <th>Посилання на закупівлю</th>
                </tr>
            </thead>
            <tbody>
            @if($questions->count())
                @foreach($questions as $question)
                    <tr>
                        <td>{{$question->title}}</td>
                        <td>{{$question->description}}</td>
                        <td>{{$question->created_at->toDateTimeString()}}</td>
                        <td>@if($question->answer) {{$question->answer}} @else Відповідь очікується @endif</td>
                        <td><a href="/tender/{{$question->tender_id}}">{{ 'tender/' . $question->tender_id }}</a></td>
                    </tr>
                @endforeach
            @endif
            </tbody>
        </table>
    </div>
</div>
@endsection