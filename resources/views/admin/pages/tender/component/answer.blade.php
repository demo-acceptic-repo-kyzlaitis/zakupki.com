    <div class="row">
        <div class="col-sm-1"></div>
        <div class="col-sm-11">
            <div class="bs-callout bs-callout-success">
                @if($question->date_answer) <h4><small>{{$question->date_answer}}</small></h4> @endif
                <p>{!! nl2br(e($question->answer)) !!}</p>
            </div>
        </div>
    </div>