<?php $same_address_disabled = isset($same_address_disabled) ? $same_address_disabled : '';
$original_address_disabled = isset($original_address_disabled) ? $original_address_disabled : 'disabled';
$additionalClassifierDisabled = ((isset($tender) && $tender && $tender->hasOneClassifier()) || (!isset($tender))) ? 'disabled' : '';?>

<div class="item-container item-section">
    <div class="col-md-12 text-left">
        <button type="button" class="close close-it" style="float: left" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
    </div>
    {!! Form::hidden("lots[$lotIndex][items][$index][id]",null)  !!}
    <div class="form-group">
        <label for="lots[{{$lotIndex}}][items][{{$index}}][description]" class="col-md-4 control-label">Конкретна назва предмета закупівлі</label>

        <div class="col-md-4">
            {!! Form::text("lots[$lotIndex][items][$index][description]",null, ['class' => 'form-control', 'placeholder' =>'', 'required', $readonly])  !!}
        </div>
        <div class="col-md-2">
            {!! Form::text("lots[$lotIndex][items][$index][quantity]",null, ['class' => 'form-control', 'placeholder' =>'к-ть', 'required', $readonly])  !!}
        </div>
        <div class="col-md-2">
            {!! Form::select("lots[$lotIndex][items][$index][unit_id]",$units,null,
            ['class' => 'form-control','required', $readonly
            ]) !!}
        </div>
    </div>
    @if($procedureType->procurement_method_type == 'aboveThresholdEU' || $procedureType->procurement_method_type == 'aboveThresholdUA.defense' || $procedureType->procurement_method_type == 'competitiveDialogueEU')
        <div class="form-group">
            <label for="lots[{{$lotIndex}}][items][{{$index}}][description]" class="col-md-4 control-label">Конкретна назва предмета закупівлі англійською</label>

            <div class="col-md-8">
                {!! Form::text("lots[$lotIndex][items][$index][description_en]",null, ['class' => 'form-control', 'placeholder' =>'', 'required', $readonly])  !!}
            </div>
        </div>
    @endif

    @if (isset($item) && $item->codes->count() > 0)
        @foreach($item->codes->sortBy('type') as $ci => $code)

            <div class="form-group @if($code->type != 1) additional-codes @endif">
                @if ($code->type == 1)
                    <label for="items[{{$index}}][{{$code->classifier->alias}}]" class="col-md-4 control-label">Класифікатор {{$code->classifier->name}}</label>
                @else
                    <div class="col-md-4">

                        <button type="button" class="close delete-classifier"
                                style="float: left; margin:6px 12px 0 15px;">
                            <span>&times;</span>
                        </button>

                        {!! Form::select("lots[$lotIndex][items][$index][additionalClassifier]", $classifiers, $code->type,
                        ['class' => 'form-control classifier-selector', 'placeholder' =>'', $readonly,'style="width:84%;"'])  !!}
                    </div>
                @endif


                <div class="col-md-8">
                    <?php $alias = $code->classifier->alias;
                    if ($code->type == 1) {
                        $classifier = '';
                        $classifier2 = '';
                    } else {
                        $alias = 'dkpp';
                        $classifier = 'classifier';
                        $classifier2 = 'classifier2';
                    }?>
                    {!! Form::text("lots[$lotIndex][items][$index][$alias]",isset($item->codes[$ci]) ? $item->codes[$ci]->code.' '.$item->codes[$ci]->description : null, ['class' => "form-control $alias $classifier", 'placeholder' =>'', $readonly])  !!}
                    {!! Form::hidden("lots[$lotIndex][items][$index][codes][$code->type][id]",$code->id, ['class' => "form-control $classifier2", 'placeholder' =>'', $readonly])  !!}
                </div>
            </div>
        @endforeach

        @if (isset($item) && $item->codes->count() == 1)
            <?php if (isset($item->tender))
                    $tender = $item->tender;?>
            <div class="form-group additional-codes @if ($additionalClassifierDisabled == 'disabled') classifier_additional hidden @endif ">
                <div class="col-md-4">

                    <button type="button" class="close delete-classifier" style="float: left; margin:6px 12px 0 15px;">
                        <span>&times;</span>
                    </button>

                    {!! Form::select("lots[$lotIndex][items][$index][additionalClassifier]", $classifiers, 2,
                    ['class' => 'form-control classifier-selector', 'placeholder' =>'', $readonly,'style="width:84%;"'])  !!}
                </div>

                <div class="col-md-8">
                    {!! Form::text("lots[$lotIndex][items][$index][dkpp]", null, ['class' => 'form-control  classifier', 'placeholder' =>'', $additionalClassifierDisabled])  !!}
                    {!! Form::hidden("lots[$lotIndex][items][$index][codes][2][id]",null, ['class' => 'form-control classifier2', 'placeholder' =>'', $additionalClassifierDisabled, $readonly])  !!}
                </div>
            </div>
        @endif
    @else

        <div class="form-group">
            <label for="lots[{{$lotIndex}}][items][{{$index}}][cpv]" class="col-md-4 control-label">Класифікатор
                ДК 021:2015</label>

            <div class="col-md-8">
                {!! Form::text("lots[$lotIndex][items][$index][cpv]", null, ['class' => 'form-control cpv', 'placeholder' =>'', 'required', $readonly])  !!}
                {!! Form::hidden("lots[$lotIndex][items][$index][codes][1][id]",null, ['class' => 'form-control', 'placeholder' =>'', $readonly])  !!}
            </div>
        </div>

        <?php if (isset($item) && isset($item->tender))
            $tender = $item->tender;?>
        <div class="form-group additional-codes @if ($additionalClassifierDisabled == 'disabled') classifier_additional hidden @endif ">
            <div class="col-md-4">

                <button type="button" class="close delete-classifier" style="float: left; margin:6px 12px 0 15px;">
                    <span>&times;</span>
                </button>

                {!! Form::select("lots[$lotIndex][items][$index][additionalClassifier]", $classifiers, 2,
                ['class' => 'form-control classifier-selector', 'placeholder' =>'', $readonly,'style="width:84%;"'])  !!}
            </div>

            <div class="col-md-8">
                {!! Form::text("lots[$lotIndex][items][$index][dkpp]", null, ['class' => 'form-control  classifier', 'placeholder' =>'', $additionalClassifierDisabled])  !!}
                {!! Form::hidden("lots[$lotIndex][items][$index][codes][2][id]",null, ['class' => 'form-control classifier2', 'placeholder' =>'', $additionalClassifierDisabled, $readonly])  !!}
            </div>
        </div>

        <!--<div class="form-group container-add-new-classifier">
            <div class="col-md-12">
                <a href="#" class="add-new-classifier text-muted">Додати класифікатор</a>
            </div>
        </div>-->
    @endif

    <div class="form-group">
        <label for="" class="col-md-4 control-label">Період доставки</label>


        <div class="col-md-4">
            <div class='input-group date'>
                {!! Form::text("lots[$lotIndex][items][$index][delivery_date_start]",null,['class' => 'form-control', 'placeholder' =>'З дд/мм/рррр',
                'date-picker',
                    'pattern'=>'\d{2}\.\d{2}\.\d{4}',
                ]) !!}
                <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
            </div>
        </div>
        <div class="col-md-4">
            <div class='input-group date'>
                {!! Form::text("lots[$lotIndex][items][$index][delivery_date_end]",null,['class' => 'form-control', 'placeholder' =>'По дд/мм/рррр',
                'date-picker',
                    'pattern'=>'\d{2}\.\d{2}\.\d{4}',
                'required'
                ]) !!}
                <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
            </div>
        </div>
    </div>

<div class="addres_block">
    <div class="form-group">
        <label for="select_address" class="col-md-4 control-label">Адреса поставки</label>

        <div class="col-md-8">
            <label  class="checkbox-inline">
                <input id="same-{{$index}}" type="radio" value="1" @if ($readonly) disabled @endif name="lots[{{$lotIndex}}][items][{{$index}}][same_delivery_address]" class="address-toggle-control same"
                       @if (!isset($item) || ((isset($item)) && $item->same_delivery_address == 1)) checked="checked" @endif>
                <span class="text_label">За адресою організації </span>
            </label>

            <label  class="checkbox-inline">
                <input type="radio" id="origin-{{$index}}" value="0" @if ($readonly) disabled @endif name="lots[{{$lotIndex}}][items][{{$index}}][same_delivery_address]" class="address-toggle-control origin"
                       @if (isset($item) && $item->same_delivery_address == 0) checked="checked" @endif>
                <span class="text_label">За іншою адресою</span>
            </label>


        </div>
    </div>

    <div class="delivery-address show-address sames" @if(isset($item) && $item->same_delivery_address == 0) style="display:none" @endif>
        <div class="form-group">
            <label for="lots[{{$lotIndex}}][items][{{$index}}][country_id]"
                   class="col-md-4 control-label">Країна</label>

            <div class="col-md-4">
                {!! Form::select("lots[$lotIndex][items][$index][country_id]",[1 => 'Україна'],1,
                ['class' => 'form-control country','readonly','disabled'
                ]) !!}
            </div>
        </div>
        <div class="form-group">
            <label for="lots[{{$lotIndex}}][items][{{$index}}][region_id]" class="col-md-4 control-label">Регіон</label>

            <div class="col-md-4">
                {!! Form::select("lots[$lotIndex][items][$index][region_id]",$regions, $organization->region_id,
                ['class' => 'form-control','readonly',$same_address_disabled ]) !!}
            </div>
        </div>
        <div class="form-group">
            <label for="lots[{{$lotIndex}}][items][{{$index}}][postal_code]"
                   class="col-md-4 control-label">Поштовий індекс</label>

            <div class="col-md-4">
                {!! Form::text("lots[$lotIndex][items][$index][postal_code]", $organization->postal_code, ['class' => 'form-control','readonly', 'placeholder' => '',$same_address_disabled])  !!}
            </div>
        </div>

        <div class="form-group">
            <label for="lots[{{$lotIndex}}][items][{{$index}}][locality]" class="col-md-4 control-label">Населений пункт</label>

            <div class="col-md-4">
                {!! Form::text("lots[$lotIndex][items][$index][locality]", $organization->locality, ['class' => 'form-control','readonly', 'placeholder' => '',$same_address_disabled])  !!}
            </div>
        </div>

        <div class="form-group">
            <label for="lots[{{$lotIndex}}][items][{{$index}}][delivery_address]"
                   class="col-md-4 control-label">Поштова адреса</label>

            <div class="col-md-4">
                {!! Form::text("lots[$lotIndex][items][$index][delivery_address]", $organization->street_address, ['class' => 'form-control','readonly', 'placeholder' => '',$same_address_disabled])  !!}
            </div>
        </div>

    </div>
    <div class="delivery-address show-address original" @if(!isset($item) || ((isset($item)) && $item->same_delivery_address == 1)) style="display:none" @endif>
        <div class="form-group">
            <label for="lots[{{$lotIndex}}][items][{{$index}}][country_id]"
                   class="col-md-4 control-label">Країна</label>


            <div class="col-md-4">
                {!! Form::select("lots[$lotIndex][items][$index][country_id]",[1 => 'Україна'],1,
                ['class' => 'form-control country','readonly','disabled'
                ]) !!}
            </div>
        </div>
        <div class="form-group">
            <label for="lots[{{$lotIndex}}][items][{{$index}}][region_id]" class="col-md-4 control-label">Регіон</label>

            <div class="col-md-4">
                {!! Form::select("lots[$lotIndex][items][$index][region_id]",$regions, isset($item->region_id) ? $item->region_id : null,
                ['class' => 'form-control regions',$original_address_disabled]) !!}
            </div>
        </div>
        <div class="form-group">
            <label for="lots[{{$lotIndex}}][items][{{$index}}][postal_code]"
                   class="col-md-4 control-label">Поштовий індекс</label>

            <div class="col-md-4">
                {!! Form::text("lots[$lotIndex][items][$index][postal_code]", null, ['class' => 'form-control address', 'placeholder' => '',$original_address_disabled])  !!}
            </div>
        </div>

        <div class="form-group">
            <label for="lots[{{$lotIndex}}][items][{{$index}}][locality]" class="col-md-4 control-label">Населений пункт</label>

            <div class="col-md-4">
                {!! Form::text("lots[$lotIndex][items][$index][locality]", null, ['class' => 'form-control address', 'placeholder' => '',$original_address_disabled])  !!}
            </div>
        </div>

        <div class="form-group">
            <label for="lots[{{$lotIndex}}][items][{{$index}}][delivery_address]"
                   class="col-md-4 control-label">Поштова адреса</label>

            <div class="col-md-4">
                {!! Form::text("lots[$lotIndex][items][$index][delivery_address]", null, ['class' => 'form-control address', 'placeholder' => '',$original_address_disabled])  !!}
            </div>
        </div>

    </div>
</div>

    <div class="features-container lots-{{$lotIndex}}-items-{{$index}}">
        @if (Session::has('_old_input'))
            @if (isset(Session::get('_old_input')["lots"][$lotIndex]['items'][$index]['features']))
                @foreach(Session::get('_old_input')["lots"][$lotIndex]['items'][$index]['features'] as $fIndex => $oldData)
                    <?php $featureIndex = count(Session::get('_old_input')["lots"][$lotIndex]['items'][$index]['features']); ?>
                    @include('pages.tender.'.$template.'.feature-form',['namespace' => "lots[$lotIndex][items][$index]", 'index' => $fIndex, 'feature' => $oldData])
                @endforeach
            @endif
        @elseif (isset($item) && $item->features->count() > 0)
            <?php $featureIndex = $item->features->count(); ?>
            @foreach($item->features as $fIndex => $feature)
                @include('pages.tender.'.$template.'.feature-form',['namespace' => "lots[$lotIndex][items][$index]", 'index' => $fIndex, 'feature' => $feature, 'tender' => $tender])
            @endforeach
        @else
            <?php $featureIndex = 0; ?>
        @endif
    </div>
    @if ($procedureType->procurement_method != 'selective')
    <div class="form-group">
        <div class="col-md-12">
            <button data-namespace="lots[{{$lotIndex}}][items][{{$index}}]"
                    data-container="lots-{{$lotIndex}}-items-{{$index}}" data-feature="{{$featureIndex}}"
                    data-proc="{{$procedureType->id}}"
                    data-template="{{$template}}" type="button"
                    class="btn btn-info pull-right add-non-price">
                <span class="glyphicon glyphicon-plus" aria-hidden="true"></span> {{Lang::get('keys.add_non_price')}}
            </button>
        </div>
    </div>
    @endif
    {{--@if($isEditable)--}}
    <b>Документація @if (is_object($procedureType) && $procedureType->procurement_method_type == 'competitiveDialogueUA' || $procedureType->procurement_method_type == 'competitiveDialogueEU' || $procedureType->procurement_method == 'selective') роботи або послуги @else товару @endif</b><br><br>
    {{--section uploading file--}}
    @include('share.component.add-file-component', ['index' => 1, 'inputName' => "lots[$lotIndex][items][$index]", 'namespace' => "lots-$lotIndex-items-$index"])
    {{--section uploading file--}}
    {{--@endif--}}
    <table class="table table-striped table-bordered">
        @if (isset($item))
            @foreach($item->documents as $document)
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
                                <a class="doc-download" href="javascript:void(0)">{{basename($document->path)}} </a>
                                <span> (Документ заватажуется до центральної бази)</span>
                            @endif
                        </td>
                        <td>
                            <div href="#" class="fileUpload btn btn-danger btn-xs helper" title-data="Редагування">
                                <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                <input type="file" name="newfiles[{{$document->id}}]" class="upload"/>
                            </div>
                        </td>
                    @endif
                </tr>
            @endforeach
        @endif
    </table>

    <hr>
</div>