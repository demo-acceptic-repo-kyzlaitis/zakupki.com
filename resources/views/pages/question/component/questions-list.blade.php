@foreach($entity->questions as $question)
    <div class=""><div class="bs-callout bs-callout-warning">
            <h4><span class="item-questions.title">{{$question->title}}</span>
                <small>{{$question->created_at}}</small></h4>
            <p>{{$question->description}}</p>
            @include('pages.question.component.answer-form')
        </div>

    </div>
    <div id="answer-cont-{{$question->id}}">
        @if(!empty($question->answer))
            @include('pages.question.component.answer')
        @endif
    </div>
@endforeach
