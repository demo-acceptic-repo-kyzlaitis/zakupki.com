<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="item-section well lot-container">
    {!! Form::hidden("lots[$index][id]",null)  !!}

    <div class="section-close">
        <span class="lot-number"></span>
        @if (!isset($tender) || $tender->status == 'draft')    <a class="btn btn-danger btn-xs close-it">&times</a>
        <div style="clear: both; float: none; margin-bottom: 10px"></div> @endif
    </div>


    <div class="item-container-{{$index}}">
        <div class="form-group">
            <label for="title" class="col-md-4 control-label">Узагальнена назва лоту</label>

            <div class="col-md-8">
                {!! Form::text("lots[$index][title]",null, ['class' => 'form-control', 'placeholder' =>'', 'required', $readonly])  !!}
            </div>
        </div>
        @if($procedureType->procurement_method_type == 'aboveThresholdEU' || $procedureType->procurement_method_type == 'aboveThresholdUA.defense' || $procedureType->procurement_method_type == 'competitiveDialogueEU')
            <div class="form-group">
                <label for="title" class="col-md-4 control-label">Узагальнена назва лоту англійською</label>

                <div class="col-md-8">
                    {!! Form::text("lots[$index][title_en]",null, ['class' => 'form-control', 'placeholder' =>'', 'required', $readonly])  !!}
                </div>
            </div>
        @endif
        <div class="form-group">
            <label for="description" class="col-md-4 control-label">Примітки лоту</label>

            <div class="col-md-8">
                {!! Form::textArea("lots[$index][description]",null,['class' => 'form-control', 'placeholder' =>'', 'rows' =>'3', $readonly
                ])  !!}
            </div>
        </div>
        @if($procedureType->procurement_method_type == 'aboveThresholdUA.defense' || $procedureType->procurement_method_type == 'competitiveDialogueEU')
            <div class="form-group">
                <label for="description_en" class="col-md-4 control-label">Примітки лоту англійською</label>

                <div class="col-md-8">
                    {!! Form::textArea("lots[$index][description_en]",null,['class' => 'form-control',
                    'placeholder' =>'',
                    'rows' =>'3',
                    'required',
                    $readonly
                    ])  !!}
                </div>
            </div>
        @endif
        <div class="budjet_amount">
            <div class="form-group">
                <label for="amount" class="col-md-4 control-label">Очікувана вартість предмета закупівлі</label>

                <div class="col-md-4">
                    {!! Form::text("lots[$index][amount]",null, ['class' => 'form-control budjet', $readonly
                    ])  !!}
                </div>
            </div>

            <div class="form-group">
                <label for="minimal_step" class="col-md-4 control-label remove-padding-top-by-7">Розмір мінімального кроку пониження ціни</label>

                <div class="col-md-4">
                    {!!Form::text("lots[$index][minimal_step]",
                                    null,
                                    ['class' => 'form-control budjet-step', $readonly

                    ])!!}
                </div>
            </div>

            <div class="form-group">
                <label for="minimal_step" class="col-md-4 control-label remove-padding-top-by-7">Розмір мінімального кроку пониження ціни в процентах</label>

                <div class="col-md-4">
                    <div class="input-group">
                        <?php if ($procedureType->threshold_type == 'below') $_minStep = '0.5% - 3%'; else $_minStep = ''; ?>
                        {!! Form::text(null,null, ['class' => 'form-control budjet-step-interest', 'placeholder' => $_minStep,
                    'maxlength'=>4, $readonly
                    ])  !!}
                        <span class="input-group-addon">%</span>
                    </div>

                </div>
            </div>
        </div>

        <hr>
        <b>Вид та розмір забезпечення тендерних пропозицій</b>
        <hr>

        <div class="guarantee-section">
                <div class="form-group " >
                    <label for="inputID" class="col-sm-4 control-label remove-padding-top-by-7">Вид забезпечення тендерних пропозицій</label>
                    <div class="col-sm-4">
                        <?php $isGuarantee = isset($lot->guarantee_amount) ? 'dbg' : 'ns'?>
                        {!! Form::select("lots[{$index}][guarantee_type]", ['ns' => 'Відсутнє', 'dbg' => 'Електронна гарантія'], $isGuarantee,
                        [ 'class' => 'form-control guarantee_type', 'id' => 'guarantee_type', $readonly]) !!}
                        {{--<select name="lots[{{$index}}][guarantee_type]" id="guarantee_type" class="form-control guarantee_type">--}}
                            {{--<option value="ns">Відсутнє</option>  --}}{{--ns - Not Specified--}}
                            {{--<option value="dbg">Електронна банківська гарантія</option>  --}}{{-- dbg - Digital Bank Guarantee --}}
                        {{--</select>--}}
                    </div>
                </div>

                <div class="form-group">
                    <label for="name" class="col-sm-4 control-label">Розмір забезпечення тендерних пропозицій</label>
                    <div class="col-sm-4">
                        <div class="input-group guarantee-currency">
                            {!! Form::input('text', "lots[{$index}][guarantee_amount]", null, ['class' => 'form-control guarantee-amount', 'disabled', $readonly]) !!}
                            {{--<input type="text" name="lots[{{$index}}][guarantee_amount]" class="form-control guarantee-amount" disabled >--}}
                            {{--{!! Form::select("lots[{$index}][guarantee_currency_id]", $currencies, 1, ['class' => 'form-control currency-select', 'disabled']) !!}--}}
                            <select name="lots[{{$index}}][guarantee_currency_id]" class="form-control currency-select" disabled >
                                @foreach($currencies as $value => $currency_name)
                                    @if($value === 1)
                                    <option value="{{$value}}" selected>{{$currency_name}}</option>
                                    @else
                                    <option value="{{$value}}" disabled>{{$currency_name}}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="name" class="col-sm-4 control-label">Розмір забезпечення тендерних пропозицій, %</label>
                    <div class="col-sm-4">
                        <div class="input-group">
                            {!! Form::input('text', "lots[{$index}][guarantee_percent]", null, ['class' => 'form-control guarantee-percent', 'disabled', $readonly]) !!}
                            {{--<input type="text" name="guarantee_percent" class="form-control guarantee-percent" disabled >--}}
                            <span class="input-group-addon">%</span>
                        </div>
                    </div>
                </div>

        </div> {{-- END OF GUARANTEE SECION --}}

        <hr>
        <b>Нецінові (якісні) критерії оцінки</b>
        <div class="features-container lots-{{$index}}">
            @if (Session::has('_old_input'))
                @if (isset(Session::get('_old_input')["lots"][$index]['features']))
                    @foreach(Session::get('_old_input')["lots"][$index]['features'] as $fIndex => $oldData)
                        <?php $featureIndex = count(Session::get('_old_input')["lots"][$index]['features']); ?>
                        @include('pages.tender.'.$template.'.feature-form',['namespace' => "lots[$index]", 'index' => $fIndex, 'feature' => $oldData])
                    @endforeach
                @endif
            @elseif (isset($lot) && $lot->features->count() > 0)
                <?php $featureIndex = $lot->features->count(); ?>
                @foreach($lot->features as $fIndex => $feature)
                    @include('pages.tender.'.$template.'.feature-form',['namespace' => "lots[$index]", 'index' => $fIndex, 'feature' => $feature, 'tender' => $tender])
                @endforeach
            @else
                <?php $featureIndex = 0; ?>
            @endif
        </div>
        @if ($procedureType->procurement_method != 'selective')
        <div class="form-group">
            <div class="col-md-12">
                <button data-template="{{$template}}" data-proc="{{$procedureType->id}}" data-namespace="lots[{{$index}}]" data-container="lots-{{$index}}" data-feature="{{$featureIndex}}" type="button" class="btn btn-info pull-right add-non-price"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> {{Lang::get('keys.add_non_price')}}</button>
            </div>
        </div>
        @endif

        {{--@if($isEditable)--}}
        <hr>
        <b>Документація лоту</b><br><br>
        {{--section uploading file--}}
        @include('share.component.add-file-component', ['index' => 1, 'inputName' => "lots[$index]", 'namespace' => "lots-$index"])
        {{--section uploading file--}}
        {{--@endif--}}
        <table class="table table-striped table-bordered">
            @if (isset($lot) && is_object($lot))
                @foreach($lot->documents as $document)
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
                                    <a href="{{$document->url}}">{{$document->title}}</a>
                                @else
                                    <a class="doc-download" href="#">{{basename($document->path)}} </a> <span>(Документ завантажується до центральної бази даних)</span>
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
        <b>Інформація про номенклатуру </b>
        <hr>
        @if (Session::has('_old_input') && isset(Session::get('_old_input')['lots'][$index]['items']))
            @foreach(Session::get('_old_input')['lots'][$index]['items'] as $itemIndex => $oldData)
                <?php $itemsIndex = count(Session::get('_old_input')['lots'][$index]['items']) - 1;?>
                @include('pages.tender.'.$template.'.item-form', ['lotIndex' => $index, 'index' => $itemIndex, 'same_address_disable' => '', 'original_address_disabled' => 'disabled'])
            @endforeach
        @elseif (isset($lot) && $lot->items->count() > 0)
            <?php $itemsIndex = $lot->items->count() - 1; ?>
            @foreach($lot->items as $itemIndex => $item)
            	@if ($item->same_delivery_address==0) 
	                @include('pages.tender.'.$template.'.item-form', ['lotIndex' => $index, 'index' => $itemIndex, 'tender' => $tender, 'same_address_disabled' => 'disabled', 'original_address_disabled' => ''])
				@endif
            	@if ($item->same_delivery_address==1) 
    	            @include('pages.tender.'.$template.'.item-form', ['lotIndex' => $index, 'index' => $itemIndex, 'tender' => $tender, 'same_address_disabled' => '', 'original_address_disabled' => 'disabled'])
				@endif				
            @endforeach
        @else
            <?php $itemsIndex = 0; ?>
            @include('pages.tender.'.$template.'.item-form', ['lotIndex' => $index, 'index' => 0, 'same_address_disabled' => '', 'original_address_disabled' => 'disabled'])
        @endif


    </div>

    @if ($procedureType->procurement_method != 'selective')
    <div class="form-group">
        <!--<button type="button" class="btn btn-info pull-right add-non-price-item">+Додати показник</button>-->
        <input type="hidden" class="item-future-index" value="0">
        <div class="col-md-4">
            <a data-lot="{{$index}}" data-item="{{$itemsIndex}}" data-template="{{$template}}" data-proc="{{$procedureType->id}}" data-organization="{{$organization->id}}" class="btn btn-success add-item-section"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> {{Lang::get('keys.add')}} @if (is_object($procedureType) && $procedureType->procurement_method_type == 'competitiveDialogueUA' || $procedureType->procurement_method_type == 'competitiveDialogueEU') {{Lang::get('keys.work_or_service')}}
                @else {{Lang::get('keys.item')}} @endif</a>
        </div>
        <div class="col-md-8"></div>
    </div>
    @endif

</div>





