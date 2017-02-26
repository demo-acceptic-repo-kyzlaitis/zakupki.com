<div class="form-group">
    {{--{!! Form::label('inputLegalName','РЕАЛЬНА НАЗВА ОРГАНІЗАЦІЇ',['class'=>'col-lg-3 control-label']) !!}--}}
    <div class="col-lg-9 col-xs-offset-3">
        {!! Form::text('legal_name',null,
           ['id'=>'inputLegalName', 'class' => 'form-control', 'placeholder' =>'РЕАЛЬНА НАЗВА ОРГАНІЗАЦІЇ',
            'required'
           ]) !!}
    </div>
</div>

<div class="form-group">
    {{--{!! Form::label('inputUri','URI',['class'=>'col-lg-3 control-label']) !!}--}}
    <div class="col-lg-9 col-xs-offset-3">
        {!! Form::text('uri',null,
           ['id'=>'inputUri', 'class' => 'form-control', 'placeholder' =>'URI',
            'required',
           ]) !!}
    </div>
</div>

<div class="form-group">
    {{--{!! Form::label('inputScheme','SCHEME',['class'=>'col-lg-3 control-label']) !!}--}}
    <div class="col-lg-9 col-xs-offset-3">
        {!! Form::text('address',null,
            ['id'=>'inputScheme', 'class' => 'form-control', 'placeholder' =>'SCHEME',
             'required'
            ]) !!}
    </div>
</div>



