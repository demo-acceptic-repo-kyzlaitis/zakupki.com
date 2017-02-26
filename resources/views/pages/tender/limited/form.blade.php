<hr>
<h3>Інформація</h3>
<br>
<div class="form-group">
    <label for="type_id" class="col-md-4 control-label">Тип процедури</label>

    <div class="col-md-8">
        <select name="type_id" id="procedure_types_id" class="form-control">
            @foreach($procedureTypes as $_procedureType)
                @if ($_procedureType->active == 1)
                    <option value="{{$_procedureType->id}}" name="type_id" @if ((isset($tender) && ($tender->type_id == $_procedureType->id))
                    || (isset($procedureType) && $procedureType->id == $_procedureType->id)) selected @endif>{{$_procedureType->procedure_name}}</option>
                @else
                    <option value="{{$_procedureType->id}}" name="type_id" disabled class="disabled-proc-type">{{$_procedureType->procedure_name}}</option>
                @endif
            @endforeach
        </select>
    </div>
</div>


@if ($procedureType->procurement_method_type != 'reporting')
    <div class="form-group">
        <label for="" class="col-sm-4 control-label">Умови застосування</label>
        <div class="col-sm-8">
            {!! Form::select("cause",$causes, null,
            [ 'class' => 'form-control'
            ]) !!}
        </div>
    </div>

    <span class="hidden" data-toggle="modal" data-target="#procedure_cause"></span>
    @include('share.component.modal-info', ['modalNamespace' => 'procedure_cause', 'modalTitle' => 'Недоступні умови застосування', 'modalMessage' => 'Умови застосування за третім пунктом статті 35 доступні лише для типу процедури Переговорна процедура скорочена'])

@endif

@if ($procedureType->procurement_method_type != 'reporting')
    <div class="form-group">
        <label for="" class="col-sm-4 control-label">Обґрунтування застосування</label>
        <div class="col-sm-8">
            {!! Form::textarea('cause_description',null, ['class' => 'form-control', 'placeholder' =>'', 'required'])  !!}
        </div>
    </div>

@endif
@if ($procedureType->procurement_method_type == 'reporting')
    <div class="form-group">
        <label for="title" class="col-md-4 control-label">Конкретна назва закупівлі</label>

        <div class="col-md-8">
            {!! Form::text('title',null, ['class' => 'form-control', 'placeholder' =>'', 'required'])  !!}
        </div>
    </div>
@else
    <div class="form-group">
        <label for="title" class="col-md-4 control-label">Узагальнена назва закупівлі</label>

        <div class="col-md-8">
            {!! Form::text('title',null, ['class' => 'form-control', 'placeholder' =>'', 'required'])  !!}
        </div>
    </div>
@endif

@if ($procedureType->procurement_method_type == 'reporting')
    <div class="form-group">
        <label for="description" class="col-md-4 control-label">Загальні відомості про закупівлю</label>

        <div class="col-md-8">
            {!! Form::textArea('description',null,['class' => 'form-control', 'placeholder' =>'',
            'rows' =>'3',
            'required'
            ])  !!}
        </div>
    </div>
@else
    <div class="form-group">
        <label for="description" class="col-md-4 control-label">Примітки</label>

        <div class="col-md-8">
            {!! Form::textArea('description',null,['class' => 'form-control', 'placeholder' =>'',
            'rows' =>'3',
            'required'
            ])  !!}
        </div>
    </div>
@endif

@if ($procedureType->procurement_method_type == 'reporting')
    <div class="budjet_amount">
        <div class="form-group">
            <label for="amount" class="col-md-4 control-label">Ціна пропозиції </label>

            <div class="col-md-4">
                {!! Form::text("amount",null, ['class' => 'form-control budjet',
                ])  !!}
            </div>
        </div>
    </div>
@else
    {{--<div class="budjet_amount">--}}
        {{--<div class="form-group">--}}
            {{--<label for="amount" class="col-md-4 control-label">Очікувана вартість закупівлі </label>--}}

            {{--<div class="col-md-4">--}}
                {{--{!! Form::text("amount",null, ['class' => 'form-control budjet',--}}
                {{--])  !!}--}}
            {{--</div>--}}
        {{--</div>--}}
    {{--</div>--}}
    @endif
    <div class="form-group">
    <label for="currency_id" class="col-md-4 control-label">Валюта</label>

    <div class="col-md-4">
        {!! Form::select("currency_id",$currencies,null,
        [ 'class' => 'form-control','required'
        ]) !!}
    </div>
    <div class="col-md-4">

        <div class="checkbox">
            <label>
                <?php
                if (isset($tender->tax_included)){
                    $in = $tender->tax_included;
                }else{
                    $in = 0;
                }
                ?>
                {!! Form::checkbox('tax_included',1,$in, [ 'placeholder' =>''])  !!} Враховуючи ПДВ
            </label>
        </div>
    </div>

</div>

<hr>

<h3>Контактні дані</h3>
<br>
@if (isset($organization))
<div class="form-group">
    <label for="title" class="col-md-4 control-label">Вибрати контактну особу</label>

    <div class="col-md-8">
        {!! Form::select("contact_person",$contacts,$mainContact,
        [ 'class' => 'form-control contact_person','required'
        ]) !!}
    </div>
</div>
<div class="contact_person">
    <div class="form-group">
        <label for="title" class="col-md-4 control-label">Контактна особа</label>

        <div class="col-md-8">
            {!! Form::text('contact_name',$organization->contact_name, ['class' => 'form-control','required', 'placeholder' =>'Петров',])  !!}
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-4 control-label">Телефон</label>

        <div class="col-md-8">
            {!! Form::text('contact_phone',$organization->contact_phone,['class' => 'form-control','required', 'placeholder' =>'+380500000000',
            ])  !!}
        </div>
    </div>
    <div class="form-group">
        <label for="description" class="col-md-4 control-label">Email</label>

        <div class="col-md-8">
            {!! Form::text('contact_email',$organization->contact_email,['class' => 'form-control','required' , 'placeholder' =>'mymail@mail.com','type'=>'email'
            ])  !!}
        </div>
    </div>
    <div class="form-group">
        <label for="description" class="col-md-4 control-label">Сайт</label>

        <div class="col-md-8">
            {!! Form::text('contact_url', null,['class' => 'form-control', 'placeholder' =>'www.mysite.com',
            ])  !!}
        </div>
    </div>
</div>
@else
    <div class="form-group">
        <label for="title" class="col-md-4 control-label">Вибрати контактну особу</label>

        <div class="col-md-8">
            {!! Form::select("contact_person",$contacts,$mainContact,
            [ 'class' => 'form-control contact_person','required'
            ]) !!}
        </div>
    </div>
    <div class="form-group">
        <label for="title" class="col-md-4 control-label">Контактна особа</label>

        <div class="col-md-8">
            {!! Form::text('contact_name',null, ['class' => 'form-control', 'placeholder' =>'',])  !!}
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-4 control-label">Телефон</label>

        <div class="col-md-8">
            {!! Form::text('contact_phone',null,['class' => 'form-control', 'placeholder' =>'',
            ])  !!}
        </div>
    </div>
    <div class="form-group">
        <label for="description" class="col-md-4 control-label">Email</label>

        <div class="col-md-8">
            {!! Form::text('contact_email',null,['class' => 'form-control', 'placeholder' =>'',
            ])  !!}
        </div>
    </div>
    <div class="form-group">
        <label for="description" class="col-md-4 control-label">Сайт</label>

        <div class="col-md-8">
            {!! Form::text('contact_url',null,['class' => 'form-control', 'placeholder' =>'',
            ])  !!}
        </div>
    </div>
@endif



{{--section uploading file--}}
{{--@if($isEditable)--}}
    <hr>
    <h3>Документація</h3>
    @include('share.component.add-file-component',['documentTypes' => $documentTypes, 'index' => 1, 'namespace' => 'tender', 'inputName' => 'tender'])
    {{--section uploading file--}}
{{--@endif--}}

<table  class="table table-striped table-bordered">
    @if (isset($tender))
        @foreach($tender->documents as $document)
            <tr>
                @if ($document->status == 'new')
					<?php
				        if ((isset($documentTypes[$document->type_id]) && $documentTypes[$document->type_id] == 'protocol') || (isset($documentTypes[$document->type_id]) && $documentTypes[$document->type_id] == 'digital_signature')) {
				            continue;
				        }
				                if (($document->type_id == 27 || $document->type_id == 29) && $tender->status == 'active.pre-qualification') {
				                    continue;
				                }
				        if (isset(pathinfo(basename($document->path))['extension'])) {
				            $type = pathinfo(basename($document->path))['extension'];
				        } elseif (isset(pathinfo(basename($document->title))['extension'])) {
				            $type = pathinfo(basename($document->title))['extension'];
				        } else {
				            $types = [
				                    "application/vnd.openxmlformats-officedocument" => 'doc',
				                    "text/plain" => 'txt',
				                    "application/x-zip-compressed" => 'zip',
				                    "application/pdf" => 'pdf',
				                    "text/html" => 'xml',
				                    "application/msword" => 'doc',
				                    "image/png" => 'png',
				                    "image/jpeg" => 'jpg',
				                    "application/vnd.ms-excel" => 'xls',
				                    "text/richtext" => 'doc',
				                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'doc'
				            ];
				            if (isset($types[$document->format])) {
				                $type = $types[$document->format];
				            } else {
				                $type = 'data';
				            }
				
				        }
			       	?>
					<td width="20%">
                		@if ($document->status != 'old')
                    		<div class="file-icon @if (isset($size)) {{$size}}@endif" data-type="{{$type}}"></div>
                		@endif
            		</td>
                    <td>
                        @if (!empty($document->url))
                            <a href="{{$document->url}}">{{basename($document->path)}} </a>
                        @else
                            <a class="doc-download" href="javascript:void(0)">{{basename($document->path)}} </a> <span>(Документ завантажується до центральної бази даних)</span>
                        @endif
                    </td>
                    <td>
                        <div href="#" class="fileUpload btn btn-danger btn-xs helper" title-data="Редагування">
                            <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                            <input type="file" name="newfiles[{{$document->id}}]" class="upload" />
                        </div>
                    </td>
                @endif
            </tr>
        @endforeach
    @endif
</table>

@if($procedureType->procurement_method_type == 'negotiation.quick' ||
           $procedureType->procurement_method_type == 'negotiation')
<hr>
<h3>Лоти</h3>
@endif
<div class="lots-container">
    @if (Session::has('_old_input'))
        @foreach(Session::get('_old_input')['lots'] as $index => $oldData)
            <?php $lotIndex = count(Session::get('_old_input')['lots']) - 1; ?>
            @include('pages.tender.'.$template.'.lot-form',['index' => $index, 'lot' => $oldData, 'currencies' => $currencies])
        @endforeach
    @elseif (isset($tender) && $tender->lots->count() > 0)
        <?php $lotIndex = $tender->lots->count() - 1; ?>
        @foreach($tender->lots as $index => $lot)
            @include('pages.tender.'.$template.'.lot-form',['index' => $index, 'lot' => $lot, 'tender' => $tender, 'currencies' => $currencies])
        @endforeach
    @else
        @include('pages.tender.'.$template.'.lot-form', ['index' => 0, 'currencies' => $currencies])
        <?php $lotIndex = 0; ?>
    @endif
</div>
{{--@if($procedureType->procurement_method_type == 'negotiation.quick' ||--}}
           {{--$procedureType->procurement_method_type == 'negotiation')--}}
{{--<div class="form-group">--}}
    {{--<div class="col-md-12">--}}
        {{--<button data-lot="{{$lotIndex}}" data-proc="{{$procedureType->id}}" data-template="{{$template}}" data-organization="{{$organization->id}}" type="button"--}}
                {{--class="btn btn-info pull-right add-lot-section"><span--}}
                    {{--class="glyphicon glyphicon-plus" aria-hidden="true"></span> {{Lang::get('keys.add_lot')}}--}}
        {{--</button>--}}
    {{--</div>--}}
{{--</div>--}}
{{--@endif--}}
<script>
    $(function(){
        $('#procedure_types_id').change(function(){
            document.location.href = '/tender/create/' + $(this).val();
        })
    })
</script>




