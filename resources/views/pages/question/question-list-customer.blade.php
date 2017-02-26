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

                @foreach($questions as $question)
                                 <tr>
                        <td>{{$question->title}}</td>
                        <td>{{$question->description}}</td>
                        <td>{{$question->created_at->toDateTimeString()}}</td>

                        <td>@if($question->tender->canQuestion())
                                @if($question->answer) {{$question->answer}}
                                @else <a href="/questions/list/{{$question->questionable->type}}/{{$question->questionable->id}}">Відповісти</a>
                                @endif
                            @else  @if($question->answer) {{$question->answer}} @else <span class="label label-danger">Запитання не було розглянуто</span> @endif
                            @endif
                        </td>
                        <td><a href="/tender/{{$question->tender_id}}">{{ 'tender/' . $question->tender_id }}</a></td>
                    </tr>
                @endforeach

            </tbody>
        </table>
        {!! $questions->render() !!}
    </div>
</div>
@endsection