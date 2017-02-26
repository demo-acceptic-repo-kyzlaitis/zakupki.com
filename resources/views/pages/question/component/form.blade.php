{!! Form::model(null,['route' => ['question.store'],
'method'=>'POST',
'enctype'=>'multipart/form-data',
'class'=>'question-form' ]) !!}
{!! Form::hidden('entity_id', $entity->id) !!}
{!! Form::hidden('entity_name', $entity->type) !!}

<div class="form-group">
    <label for="title">Тема</label>
    {!! Form::text('title',null,['class' => 'form-control','required'=>'required', 'placeholder' =>'', 'rows' =>'3'])  !!}
</div>
<div class="form-group">
    <label for="question">Запитання</label>
    {!! Form::textArea('question',null,['class' => 'form-control','required'=>'required', 'placeholder' =>'', 'rows' =>'3'])  !!}
</div>
<div class="form-group">
    {!! Form::submit(Lang::get('keys.publish_question'),['class'=>'btn btn-xs btn-default']) !!}
</div>
</fieldset>
{!! Form::close() !!}
