@if (Auth::check() && ($tender->isOwner(Auth::user()->id)) && empty($question->answer))
    @if (strtotime($tender->enquiry_end_date) < time())
        <p class="alert alert-danger">Для того, щоб відповісти на запитання, продовжіть кінцевий термін подачі
            пропозицій
            тендера.</p>
    @else
        <p><a href="#" class="btn give-answer btn-info">{{Lang::get('keys.answer')}}</a></p>
        <div class="answer well" id="answer-{{$question->id}}">
            {!! Form::model($question,['route' => ['questions.answer', $question->id],
            'method'=>'POST',
            'enctype'=>'multipart/form-data',
            'class'=>'answer-form', 'qid' => $question->id]) !!}
            <div class="form-group">
                {!! Form::textArea('answer',null,['class' => 'form-control', 'placeholder' =>'', 'rows' =>'3'])  !!}
            </div>
            <div class="form-group">
                {!! Form::submit(Lang::get('keys.send'),['class'=>'btn btn-xs btn-default']) !!}
            </div>
            </fieldset>
            {!! Form::close() !!}
        </div>
    @endif
@endif
