<?php
    // иногда юзеру жмут кнопуку "Активувати зміну" сразу от чего бывают ошибки. Необходимая последовательность "Зберегти" -> "Активувати зміну"
    $disabled    = $contract->change && $contract->change->cbd_id ? '' : 'disabled';
    $showMessage = $contract->change && $contract->change->cbd_id ? '' : 'changes-modal';
?>

{!! Form::model($contract,['route' => ['contract.update', $contract->id],
'method'=>'PUT',
'enctype'=>'multipart/form-data',
'class'=>'form-horizontal',]) !!}
@if ($contract->change)
    <input type="hidden" name="change[id]" value="{{$contract->change->id}}">
@endif
<fieldset>

    <div class="form-group">
        <label for="reason" class="col-sm-4 control-label">Номер договору</label>

        <div class="col-lg-4">

            {!! Form::text('change[contract_number]', null, ['class' => 'form-control', 'placeholder' => '', 'required'])  !!}
        </div>
    </div>
    <div class="form-group">
        <label for="reason" class="col-sm-4 control-label">Випадки для внесення змін до істотних умов договору</label>

        <div class="col-lg-4">

            {!! Form::select("change[rationale_type_id]",$types,null,
                    ['class' => 'form-control','required'
                    ]) !!}
        </div>
    </div>
    <div class="form-group">
        <label for="reason" class="col-sm-4 control-label">Опис змін, що внесені до істотних умов договору</label>

        <div class="col-lg-4">

            {!! Form::text('change[rationale]', null, ['class' => 'form-control', 'placeholder' => '', 'required'])  !!}
        </div>
    </div>


    <div class="form-group">
        <label for="reason" class="col-sm-4 control-label">Сума</label>

        <div class="col-md-4">
            <div class='input-group date'>
                {!! Form::text("amount",null,['class' => 'form-control']) !!}
                <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
            </div>
        </div>

    </div>

    <div class="form-group">
        <label for="reason" class="col-sm-4 control-label">Дата підписання</label>

        <div class="col-md-4">
            <div class='input-group date'>
                {!! Form::text("change[date_signed]",null,['class' => 'form-control', 'placeholder' =>'дд/мм/рррр',
                'date-time-picker',
                'pattern'=>'\d{2}\.\d{2}\.\d{4} \d{2}:\d{2}',
                'required'
                ]) !!}
                <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
            </div>
        </div>

    </div>

    <div class="form-group">
        <label class="col-md-4 control-label">Строк дії договору</label>

        <div class="col-md-2">
            <div class='input-group date'>
                {!! Form::text('period_date_start',null,['class' => 'form-control', 'placeholder' =>'З дд/мм/гггг',
                'date-picker',
                    'pattern'=>'\d{2}\.\d{2}\.\d{4}',
                    'locale' => 'ru',
                'required'
                ]) !!}
                <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
            </div>
        </div>
        <div class="col-md-2">
            <div class='input-group date'>
                {!! Form::text('period_date_end',null,['class' => 'form-control', 'placeholder' =>'По дд/мм/гггг',
                'date-picker',
                'pattern'=>'\d{2}\.\d{2}\.\d{4}',
                'required'
                ]) !!}<span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
            </div>
        </div>
    </div>



    @if ($contract->documents()->count())
        <div class="form-group">
            <div class="col-md-8 col-md-offset-4">
                @include('share.component.document-list', ['entity' => $contract, 'size' => 'file-icon-sm', 'edit' => true])
            </div></div>



    @endif

    @include('share.component.add-file-component',['documentTypes' => [], 'index' => 0, 'namespace' => 'contract', 'inputName' => 'contract', '_buttonName' => 'Додати скан договору'])


    <hr>
    <div class="form-group">
        <div class="col-lg-12 text-center">
            {!! Form::submit(Lang::get('keys.save'),['class'=>'btn btn-info']) !!}
            <a @if(!$disabled && !$showMessage)  href="{{route('contract.activate', [$contract->id])}}"  @else data-toggle="modal" data-target="#{{$showMessage}}" @endif class="btn btn-success" {{$disabled}} >{{Lang::get('keys.activate_change')}}</a>
        </div>
    </div>

    @if($showMessage)
        @include('share.component.modal-info', ['modalNamespace' => $showMessage ,'modalTitle' => '', 'modalMessage' => 'Для активації змін до договору додайте документ та натисніть Зберегти.'])
    @endif

</fieldset>
{!! Form::close() !!}


