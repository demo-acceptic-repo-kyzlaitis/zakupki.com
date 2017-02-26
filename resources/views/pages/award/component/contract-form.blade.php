@if($errors->has())
    <div class="alert alert-danger" role="alert">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{!! Form::model($contract,['route' => ['contract.update', $contract->id],
'method'=>'PUT',
'enctype'=>'multipart/form-data',
'class'=>'form-horizontal',]) !!}
<span class="place">
</span>

<fieldset>

    <div class="form-group">
        <label for="reason" class="col-sm-4 control-label">Номер договору</label>

        <div class="col-lg-4">
            @if ($contract->tender->blocked)
                {!! Form::text('contract_number', null, ['disabled' => 'disabled', 'class' => 'form-control', 'placeholder' => '', 'required'])  !!}
                @else
                {!! Form::text('contract_number', null, ['class' => 'form-control', 'placeholder' => '', 'required'])  !!}
                @endif
        </div>
    </div>

    <div class="form-group">
        <label for="reason" class="col-sm-4 control-label">Дата підписання</label>

        <div class="col-md-4">
            <div class='input-group date'>
                @if ($contract->tender->blocked)
                    {!! Form::text("date_signed",null,['disabled' => 'disabled', 'class' => 'form-control', 'placeholder' =>'дд/мм/рррр',
                'date-time-picker',
                'pattern'=>'\d{2}\.\d{2}\.\d{4} \d{2}:\d{2}',
                'required'
                ]) !!}
                @else
                    {!! Form::text("date_signed",null,['class' => 'form-control', 'placeholder' =>'дд/мм/рррр',
                'date-time-picker',
                'pattern'=>'\d{2}\.\d{2}\.\d{4} \d{2}:\d{2}',
                'required'
                ]) !!}
                @endif

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

    {{--//todo comment out for amount per item task author illia--}}
    {{--@if($contract->tender->procedureType->procurement_method_type == 'reporting')--}}
        {{--<div class="form-group">--}}
            {{--<label for="reason" class="col-sm-4 control-label">Ціна за одиницю</label>--}}

            {{--<div class="col-md-4">--}}

                    {{--{!! Form::number("amount_per_item", null, ['class' => 'form-control',--}}
                                                                {{--'placeholder' =>''])--}}
                    {{--!!}--}}
            {{--</div>--}}

        {{--</div>--}}
    {{--@endif--}}


@if ($contract->documents()->count())
    <div class="form-group">
        <div class="col-md-8 col-md-offset-4">
            @include('share.component.document-list', ['entity' => $contract, 'size' => 'file-icon-sm', 'edit' => true])
        </div></div>



    @endif

    @include('share.component.add-file-component',['procedureType' => $contract->tender->procedureType, 'documentTypes' => \App\Model\DocumentType::where('namespace', 'contract')->get(), 'index' => 0, 'namespace' => 'contract', 'inputName' => 'contract', '_buttonName' => 'Додати скан договору'])


    <hr>
    @if ($contract->award->complaints()->where('status', 'pending')->count() > 0)
        <div class="container"><div class="alert alert-danger" role="alert">
                У цій закупівлі є скарги без відповіді.
            </div></div>
    @else
    <div class="form-group">
        <div class="col-lg-12 text-center">
            {!! Form::submit(Lang::get('keys.save'),['class'=>'btn btn-info']) !!}
            <?php
                $hasDocs = $contract->documents()->where('format', '!=', 'application/pkcs7-signature')->count();
            ?>
            @if ($contract->tender->procedureType->procurement_method_type != 'reporting' && $hasDocs)
                @if ($contract->tender->procedureType->threshold_type == 'below' || $contract->tender->procedureType->threshold_type == 'below.limited')
            		<a data-href="{{route('contract.activate', [$contract->id])}}" href="#"
                       data-toggle="modal" data-target="#delete{{$contract->id}}"
                       class="btn btn-success" id="signature-confirm"
                       @if (!$contract->contract_number) disabled @endif>{{Lang::get('keys.sign_contract')}}
                    </a>
                    <a href="#" class="btn btn-success"  data-toggle="modal"
                    @if ($contract->contract_number)
                    	data-target="#signature-set"
                       data-id="{{$contract->id}}" data-documentname="contract"
                    @else
                    	disabled
                    @endif
                    >Підписати ЕЦП</a>
                @else
            		<a data-href="{{route('contract.activate', [$contract->id])}}" href="#"
                       data-toggle="modal" data-target="#delete{{$contract->id}}"
                       class="btn btn-success hidden" id="signature-confirm" @if (!$contract->contract_number) disabled @endif>{{Lang::get('keys.sign_contract')}}
                    </a>
                    <a href="#" class="btn btn-success"  data-toggle="modal"
                    @if ($contract->contract_number)
                    	data-target="#signature-set"
                       data-id="{{$contract->id}}" data-documentname="contract"
                    @else
                       disabled
                    @endif
                    >{{Lang::get('keys.sign_ecp')}}</a>
                @endif
                    @include('share.component.modal-confirm', ['modalNamespace' => $contract->id, 'modalTitle' => 'Підписати договір і завершити процедуру', 'modalMessage' => 'Ця операція призведе до завершення процедури закупівлі, після чого внесення змін до процедури стане неможливим. Ви підтверджуєте підписання договору?'])
            @elseif ($contract->tender->procedureType->procurement_method_type == 'reporting')
            		<a data-href="{{route('contract.activate', [$contract->id])}}" href="#"
                       data-toggle="modal" data-target="#delete{{$contract->id}}"
                       class="btn btn-success hidden" id="signature-confirm" @if (!$contract->contract_number) disabled @endif>{{Lang::get('keys.sign_contract')}}
                    </a>
                    @include('share.component.modal-confirm', ['modalNamespace' => $contract->id, 'modalTitle' => 'Підписати договір і завершити процедуру', 'modalMessage' => 'Ця операція призведе до завершення процедури закупівлі, після чого внесення змін до процедури стане неможливим. Ви підтверджуєте підписання договору?'])
                    <a href="#" class="btn btn-success"  data-toggle="modal"
                    @if ($contract->contract_number)
                    	data-target="#signature-set"
                       data-id="{{$contract->id}}" data-documentname="contract"
                    @else
                    	disabled
                    @endif
                    >{{Lang::get('keys.sign_ecp')}}</a>
            @endif
        </div>
    </div>
    @endif


</fieldset>
{!! Form::close() !!}


<!-- add IIT library -->
    	<script type="text/javascript" src="/js/sign/euscpt.js"></script>
    	<script type="text/javascript" src="/js/sign/euscpm.js"></script>
    	<script type="text/javascript" src="/js/sign/euscp.js" async></script>


    	<!-- add public script from openprocurement-crypto for prepareObject() -->
    	<script type="text/javascript" src="https://cdn.rawgit.com/openprocurement-crypto/common/v.0.0.23/js/index.js"></script>

		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/ladda-bootstrap/0.9.4/spin.min.js"></script>
		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/ladda-bootstrap/0.9.4/ladda.min.js"></script>

		<script type="text/javascript" src="/js/sign/promise.min.js"></script>
		<script type="text/javascript" src="/js/sign/jsondiffpatch.min.js"></script>
		<script type="text/javascript" src="/js/sign/signer.js"></script>

@include('share.component.modal-ecp')
