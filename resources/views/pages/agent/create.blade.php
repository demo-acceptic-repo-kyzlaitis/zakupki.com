@extends('layouts.index')

@section('content')

{{--<script id="code-2015-id-tpl" type="text/x-jquery-tmpl">--}}
    <div class="code2015-item form-group" id="code2015-item-template" style="display: none" >
        <label for="dk2015" class="col-sm-3 control-label">  </label>
        <div class="col-sm-6">
            {!! Form::text('', null, ['class' => 'form-control input-code2015 agent-classifier' , 'placeholder' =>'', 'required', 'id' => 'code2015'])  !!}
            {!! Form::hidden('',null, ['class' => 'form-control classifier2 input-code2015-hidden', 'placeholder' =>''])  !!}
        </div>
        <div class="col-sm-1">
            <button class="btn btn-danger form-control remove-codes2015" type="button"><i class="glyphicon glyphicon-minus"></i></button>
        </div>
    </div>


    <div class="code2010-item form-group" id="code2010-item-template" style="display: none" >
        <label for="dk2015" class="col-sm-3 control-label">   </label>
        <div class="col-sm-6">
            {!! Form::text('', null, ['class' => 'form-control agent-classifier-2010', 'placeholder' =>'', 'required', 'id' => 'code2010'])  !!}
            {!! Form::hidden('',null, ['class' => 'form-control classifier2 input-code2010-hidden', 'placeholder' =>''])  !!}
        </div>

        <div class="col-sm-1">
            <button class="btn btn-danger form-control remove-codes2010" type="button"><i class="glyphicon glyphicon-minus"></i></button>
        </div>
    </div>
{{--</script>--}}

    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <h2>Створення запита для пошукового агента</h2>
            </div>
        </div>
        <hr>

        <div class="row">
            {!! Form::open(['url' => route('agent.store'),
                'method'=>'POST',
                'enctype'=>'multipart/form-data',
                'class'=>'form-horizontal',
                'id' => 'agent-store']) !!}

            <div class="form-group">
                <label for="field" class="col-sm-3 control-label">Сфера дільності </label>
                <div class="col-sm-6">
                    {!! Form::text('field', null, ['class' => 'form-control', 'placeholder' =>'', 'required', 'id' => 'field'])  !!}
                </div>
            </div>

            <div class="form-group">
                <label for="comment" class="col-sm-3 control-label">Регіон </label>
                <div class="col-sm-6">
                    {!! Form::select('region_id', $regions, null, ['class' => 'form-control', 'placeholder' =>'', 'required', 'id' => 'region', ])  !!}
                </div>
            </div>

            <div class="code2015-set" data-set-amount="0" id="code2015-set">
                <div class="form-group">
                    <div class="code2015-item">
                        <label for="dk2015" class="col-sm-3 control-label">Код класифікатора ДК 021:2015 </label>
                        <div class="col-sm-6">
                            {!! Form::text('codes2015[0]', null, ['class' => 'form-control input-code2015 agent-classifier', 'placeholder' =>'', 'required', 'id' => 'code2015'])  !!}
                            {!! Form::hidden('codes2015[0]',null, ['class' => 'form-control classifier2 input-code2015-hidden', 'placeholder' =>''])  !!}
                        </div>
                        <div class="col-sm-1">
                            <button class="btn btn-success form-control add-codes2015" type="button"><i class="glyphicon glyphicon-plus"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="code2010-set" data-set-amount="0" id="code2010-set">
                <div class="form-group">
                    <div class="code2010-item">
                        <label for="dk2010" class="col-sm-3 control-label">Код класифікатора ДК 016:2010 </label>
                        <div class="col-sm-6">
                            {!! Form::text('codes2010[0]', null, ['class' => 'form-control agent-classifier-2010', 'placeholder' =>'', 'required', 'id' => 'dk2010'])  !!}
                            {!! Form::hidden('codes2010[0]',null, ['class' => 'form-control classifier2', 'placeholder' =>''])  !!}
                        </div>
                        <div class="col-sm-1">
                            <button class="btn btn-success form-control add-codes2010" type="button"><i class="glyphicon glyphicon-plus"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="start_amount" class="col-sm-3 control-label">Вартість закупівлі від </label>
                <div class="col-sm-6">
                    {!! Form::text('start_amount', null, ['class' => 'form-control', 'placeholder' =>'', 'required', 'id' => 'start_amount', 'type' => 'number'])  !!}
                </div>
            </div>

            <div class="form-group">
                <label for="end_amount" class="col-sm-3 control-label">Вартість закупівлі до </label>
                <div class="col-sm-6">
                    {!! Form::text('end_amount', null, ['class' => 'form-control', 'placeholder' =>'', 'required', 'id' => 'end_amount'])  !!}
                </div>
            </div>

            <div class="form-group">
                <label for="comment" class="col-sm-3 control-label">Коментар </label>
                <div class="col-sm-6">
                    {!! Form::textarea('comment', null, ['class' => 'form-control', 'placeholder' =>'', 'required', 'id' => 'comment', 'rows' => 3])  !!}
                </div>
            </div>



            <div class="form-group">
                <div class="col-sm-3 col-sm-offset-6">
                    <button type="submit" class="btn btn-primary pull-right">{{Lang::get('keys.send')}}</button>
                </div>
            </div>
            {!! Form::close() !!}

        </div>
    </div>



@endsection