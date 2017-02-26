<hr>
<h3>Інформація</h3>
<br>
<?php $readonly = '';
if ($procedureType->procurement_method == 'selective') $readonly = 'readonly';
if (isset($tender) && $tender->tenderContacts->count() > 1)
    $additionalContact = $tender->tenderContacts->last()->contact;
if (!isset($tender))
    $additionalContact = $organization;
?>
<div class="form-group">
    <label for="type_id" class="col-md-4 control-label">Тип процедури</label>

    <div class="col-md-8">
        <select name="type_id" id="procedure_types_id" class="form-control" {{$readonly}}>
            @foreach($procedureTypes as $_procedureType)
                @if ($_procedureType->active == 1 || (isset($tender) && $tender->type_id == $_procedureType->id))
                    <option value="{{$_procedureType->id}}" name="type_id" @if ((isset($tender) && ($tender->type_id == $_procedureType->id))
                    || (is_object($procedureType) && $procedureType->id == $_procedureType->id)) selected @endif>{{$_procedureType->procedure_name}}</option>
                @else
                    <option value="{{$_procedureType->id}}" name="type_id" disabled class="disabled-proc-type">{{$_procedureType->procedure_name}}</option>
                @endif
            @endforeach
        </select>
    </div>
</div>

@if ($procedureType->procurement_method_type == 'belowThreshold')
    <div class="form-group">
        <label for="" class="col-sm-4 control-label">Тип предмету закупівлі</label>

        <div class="col-sm-8">
            {!! Form::select("procurement_type_id",$procurementType, null,
            [ 'class' => 'form-control procurement_type', $readonly
            ]) !!}
            {{--<select name="procurement_type_id" class="form-control procurement_type">--}}
            {{--@foreach($procurementType as $id => $procName)--}}
            {{--<option value="{{ $id }}" @if (old('procurement_type_id') == $id) selected="selected" @endif>{{ $procName }}</option>--}}
            {{--@endforeach--}}
            {{--</select>--}}
        </div>
    </div>
@endif

<div class="form-group">
    <label for="title" class="col-md-4 control-label">Узагальнена назва закупівлі</label>

    <div class="col-md-8">
        {!! Form::text('title',null, ['class' => 'form-control', 'placeholder' =>'', 'required', $readonly])  !!}
    </div>
</div>

@if(is_object($procedureType) && ($procedureType->procurement_method_type == 'aboveThresholdEU' || $procedureType->procurement_method_type == 'aboveThresholdUA.defense' || $procedureType->procurement_method_type == 'competitiveDialogueEU'))
    <div class="form-group">
        <label for="title" class="col-md-4 control-label">Узагальнена назва закупівлі англійською</label>

        <div class="col-md-8">
            {!! Form::text('title_en',null, ['class' => 'form-control', 'placeholder' =>'', 'required', $readonly])  !!}
        </div>
    </div>
@endif

<div class="form-group">
    <label for="description" class="col-md-4 control-label">Примітки</label>

    <div class="col-md-8">
        {!! Form::textArea('description',null,['class' => 'form-control', 'placeholder' =>'',
        'rows' =>'3', $readonly])  !!}
    </div>
</div>

@if(is_object($procedureType) && ($procedureType->procurement_method_type == 'aboveThresholdEU' || $procedureType->procurement_method_type == 'aboveThresholdUA.defense' || $procedureType->procurement_method_type == 'competitiveDialogueEU'))
    <div class="form-group">
        <label for="description" class="col-md-4 control-label">Примітки англійською</label>

        <div class="col-md-8">
            {!! Form::textArea('description_en',null,['class' => 'form-control', 'placeholder' =>'',
            'rows' =>'3',
            'required',
            $readonly
            ])  !!}
        </div>
    </div>
@endif

<div class="form-group">
    <label for="currency_id" class="col-md-4 control-label">Валюта</label>

    <div class="col-md-4">
        {!! Form::select("currency_id",$currencies,null,
        [ 'class' => 'form-control','required', $readonly
        ]) !!}
    </div>
    <div class="col-md-4">

        <div class="checkbox">
            <label>
                <?php
                if (isset($tender->tax_included)) {
                    $in = $tender->tax_included;
                } else {
                    $in = 0;
                }
                ?>
                {!! Form::checkbox('tax_included',1,$in, [ 'placeholder' =>'', ($readonly) ? 'disabled' : ''])  !!} Враховуючи ПДВ
            </label>
        </div>
    </div>

</div>

<hr>

<h3>Дані контактної особи замовника</h3>
<br>
@if (isset($organization) && !isset($tender))
    <div class="contacts">
        <div class="contact_main contact">
            <div class="col-md-12 text-left closer" style="display:none">
                <button type="button" class="close close-cont" style="float: left" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            </div>
            <div class="form-group">
                <label for="title" class="col-md-4 control-label">Вибрати контактну особу</label>
                <div class="col-md-8">
                    {!! Form::select("contact_person",$contacts, $mainContact,
                    [ 'class' => 'form-control contact_person','required',
                    ]) !!}

                </div>
                <input type="hidden" class="cuurentId" value="{{$mainContact}}">
            </div>
            <div class="form-group">
                <label for="title" class="col-md-4 control-label">Контактна особа</label>
                <div class="col-md-8">
                    {!! Form::text('contact_name',$organization->contact_name, ['class' => 'form-control contact_name','required','readonly', 'placeholder' =>'Петров',])  !!}
                </div>
            </div>
            @if(is_object($procedureType) && $procedureType->procurement_method_type == 'aboveThresholdEU' || $procedureType->procurement_method_type == 'competitiveDialogueEU' || $procedureType->procurement_method_type == 'competitiveDialogueEU.stage2')
                <div class="form-group">
                    <label for="title" class="col-md-4 control-label">Контактна особа англ.</label>

                    <div class="col-md-8">
                        {!! Form::text('contact_name_en',$organization->contact_name_en, ['class' => 'form-control contact_name_en','required','readonly', 'placeholder' =>'Petrov',])  !!}
                    </div>
                </div>
            @endif
            <div class="form-group">
                <label class="col-md-4 control-label">Телефон</label>

                <div class="col-md-8">
                    {!! Form::text('contact_phone',$organization->contact_phone,['class' => 'form-control contact_phone','required','readonly', 'placeholder' =>'+380500000000',
                    ])  !!}
                </div>
            </div>
            <div class="form-group">
                <label for="description" class="col-md-4 control-label">Email</label>

                <div class="col-md-8">
                    {!! Form::text('contact_email',$organization->contact_email,['class' => 'form-control contact_email','required' ,'readonly', 'placeholder' =>'mymail@mail.com','type'=>'email'
                    ])  !!}
                </div>
            </div>
            <div class="form-group">
                <label for="description" class="col-md-4 control-label">Сайт</label>

                <div class="col-md-8">
                    {!! Form::text('contact_url',$organization->contact_url,['class' => 'form-control contact_url','readonly', 'placeholder' =>'www.mysite.com',
                    ])  !!}
                </div>
            </div>
            @if(is_object($procedureType) && $procedureType->procurement_method_type == 'aboveThresholdEU' || $procedureType->procurement_method_type == 'competitiveDialogueEU' || $procedureType->procurement_method_type == 'competitiveDialogueEU.stage2')
                <div class="form-group">
                    <label for="title" class="col-md-4 control-label">Мова</label>

                    <div class="col-md-8">
                        {!! Form::select("contact_available_lang",$languages, $organization->contact_available_lang,
                        [ 'class' => 'form-control contact_available_lang','required'
                        ]) !!}
                    </div>
                </div>
            @endif
        </div>
        @else
            <div class="contacts">
                <div class="contact_main contact">
                    <div class="col-md-12 text-left closer" style="display:none">
                        <button type="button" class="close close-cont" style="float: left" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    </div>
                    <div class="form-group">
                        <label for="title" class="col-md-4 control-label">Вибрати контактну особу</label>
                        <div class="col-md-8">

                            {!! Form::select("contact_person",$contacts,$mainContact,
                            [ 'class' => 'form-control contact_person','required'
                            ]) !!}
                        </div>
                        <input type="hidden" class="cuurentId" value="{{$mainContact}}">
                    </div>
                    <div class="form-group">
                        <label for="title" class="col-md-4 control-label">Контактна особа</label>
                        <div class="col-md-8">
                            {!! Form::text('contact_name',null, ['class' => 'form-control contact_name','required','readonly', 'placeholder' =>'Петров',])  !!}
                        </div>
                    </div>
                    @if(is_object($procedureType) && $procedureType->procurement_method_type == 'aboveThresholdEU' || $procedureType->procurement_method_type == 'competitiveDialogueEU' || $procedureType->procurement_method_type == 'competitiveDialogueEU.stage2')
                        <div class="form-group">
                            <label for="title" class="col-md-4 control-label">Контактна особа англ.</label>

                            <div class="col-md-8">
                                {!! Form::text('contact_name_en',null, ['class' => 'form-control contact_name_en','required','readonly', 'placeholder' =>'Petrov',])  !!}
                            </div>
                        </div>
                    @endif
                    <div class="form-group">
                        <label class="col-md-4 control-label">Телефон</label>

                        <div class="col-md-8">
                            {!! Form::text('contact_phone',null,['class' => 'form-control contact_phone','required','readonly', 'placeholder' =>'+380500000000',
                            ])  !!}
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="description" class="col-md-4 control-label">Email</label>

                        <div class="col-md-8">
                            {!! Form::text('contact_email',null,['class' => 'form-control contact_email','required' ,'readonly', 'placeholder' =>'mymail@mail.com','type'=>'email'
                            ])  !!}
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="description" class="col-md-4 control-label">Сайт</label>

                        <div class="col-md-8">
                            {!! Form::text('contact_url',null,['class' => 'form-control contact_url','readonly', 'placeholder' =>'www.mysite.com',
                            ])  !!}
                        </div>
                    </div>
                    @if(is_object($procedureType) && $procedureType->procurement_method_type == 'aboveThresholdEU' || $procedureType->procurement_method_type == 'competitiveDialogueEU' || $procedureType->procurement_method_type == 'competitiveDialogueEU.stage2')
                        <div class="form-group">
                            <label for="title" class="col-md-4 control-label">Мова</label>

                            <div class="col-md-8">
                                {!! Form::select("contact_available_lang",$languages, $organization->contact_available_lang,
                                [ 'class' => 'form-control contact_available_lang','required'
                                ]) !!}
                            </div>
                        </div>
                    @endif
                </div>
                @endif

                @if (is_object($procedureType) && $procedureType->procurement_method_type == 'competitiveDialogueEU.stage2' || $procedureType->procurement_method_type == 'competitiveDialogueEU')

                    <hr>
                    <h4>Додаткові контакти</h4>
                @endif


                <?php $numberOfcontact = 0; ?>
                @if(isset($additionalContacts) && count($additionalContacts) > 0)
                    @foreach($additionalContacts as $con)

                        <div class="contact">
                            <div class="col-md-12 text-left closer">
                                <button type="button" class="close close-cont" style="float: left" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                            </div>
                            <div class="form-group">
                                <label for="title" class="col-md-4 control-label">Вибрати контактну особу</label>
                                <div class="col-md-8">
                                    {!! Form::select("",$contacts, $con->contact->id,
                                    [ 'class' => 'form-control contact_person','required',
                                    ]) !!}
                                </div>
                                <input type="hidden" name="additional_contact[{{$numberOfcontact}}][id]" class="cuurentId" value="{{$con->contact->id}}">
                            </div>
                            <div class="form-group">
                                <label for="title" class="col-md-4 control-label">Контактна особа</label>
                                <div class="col-md-8">
                                    {!! Form::text('',$con->contact->contact_name, ['class' => 'form-control contact_name','required','readonly', 'placeholder' =>'Петров',])  !!}
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-4 control-label">Телефон</label>
                                <div class="col-md-8">
                                    {!! Form::text('',$con->contact->contact_phone,['class' => 'form-control contact_phone','required','readonly', 'placeholder' =>'+380500000000',
                                    ])  !!}
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="description" class="col-md-4 control-label">Email</label>
                                <div class="col-md-8">
                                    {!! Form::text('',$con->contact->contact_email,['class' => 'form-control contact_email','required' ,'readonly', 'placeholder' =>'mymail@mail.com','type'=>'email'
                                    ])  !!}
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="description" class="col-md-4 control-label">Сайт</label>
                                <div class="col-md-8">
                                    {!! Form::text('',$con->contact->contact_url,['class' => 'form-control contact_url','readonly', 'placeholder' =>'www.mysite.com',
                                    ])  !!}
                                </div>
                            </div>
                        </div>

                        <?php $numberOfcontact++;?>
                    @endforeach
                @endif
            </div>

            @if (is_object($procedureType) && $procedureType->procurement_method_type == 'competitiveDialogueEU.stage2' || $procedureType->procurement_method_type == 'competitiveDialogueEU' || $procedureType->procurement_method_type == 'aboveThresholdUA.defense')

                <div class="form-group button-contact">
                    <div class="col-md-12">
                        <input type="hidden" class="count_added_contact" value="{{$numberOfcontact}}">
                        <button type="button"
                                class="btn btn-info pull-right add-contact"><span
                                    class="glyphicon glyphicon-plus" aria-hidden="true"></span> {{Lang::get('keys.add_new_contact')}}
                        </button>
                    </div>
                </div>
            @endif


            <hr>
            <h3>Дати</h3>
            <br>

            @if (is_object($procedureType) && $procedureType->procurement_method_type == 'belowThreshold')
                <div class="form-group">
                    <label class="col-md-4 control-label">Закінчення періоду уточнень</label>

                    <div class="col-md-8 ">
                        <div class='input-group date' id="enquire-date-time">
                            {!! Form::text('enquiry_end_date',null,['class' => 'form-control', 'placeholder' =>'По дд.мм.рррр',
                            'pattern'=>'\d{2}\.\d{2}\.\d{4} \d{2}:\d{2}',
                            'required'
                            ]) !!}<span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                        </div>
                    </div>
                </div>
            @endif


            <div class="form-group ">
                <label class="col-md-4   control-label">
                    Кінцевий строк подання тендерних пропозицій
                </label>

                <div class="col-md-8 ">
                    <div class='input-group date' id="tendering-date-time">
                        {!! Form::text('tender_end_date',null,['class' => 'form-control', 'placeholder' =>'По дд.мм.рррр',
                            'pattern'=>'\d{2}\.\d{2}\.\d{4} \d{2}:\d{2}',
                            'required'
                        ]) !!}
                        <span class="input-group-addon">
                <span class="glyphicon glyphicon-calendar"></span>
            </span>
                    </div>
                </div>
            </div>

            {{--section uploading file--}}
            {{--@if($isEditable)--}}
            <hr>
            <h3>Документація</h3>
            @include('share.component.add-file-component',['documentTypes' => $documentTypes, 'index' => 1, 'namespace' => 'tender', 'inputName' => 'tender'])
            {{--section uploading file--}}
            {{--@endif--}}
            <table class="table table-striped table-bordered">
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
                                        <input type="file" name="newfiles[{{$document->id}}]" class="upload"/>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                @endif
            </table>


            <hr>
            <h3>Нецінові показники</h3>

            <div class="features-container tender">
                @if (Session::has('_old_input') && isset(Session::get('_old_input')['features']))
                    @foreach(Session::get('_old_input')['features'] as $index => $oldData)
                        <?php $featureIndex = count(Session::get('_old_input')['features']); ?>
                        @include('pages.tender.'.$template.'.feature-form',['namespace' => 'tender', 'index' => $index, 'feature' => $oldData])
                    @endforeach
                @elseif (isset($tender) && $tender->features->count() > 0)
                    <?php $featureIndex = $tender->features->count(); ?>
                    @foreach($tender->features as $index => $feature)
                        @include('pages.tender.'.$template.'.feature-form',['namespace' => 'tender', 'index' => $index, 'feature' => $feature, 'tender' => $tender])
                    @endforeach
                @else
                    <?php $featureIndex = 0; ?>
                @endif
            </div>
            @if ($procedureType->procurement_method != 'selective')
                <div class="form-group">
                    <div class="col-md-12">
                        <button data-namespace="tender" data-container="tender" data-proc="{{$procedureType->id}}"
                                data-feature="{{$featureIndex}}" type="button"
                                data-template="{{$template}}"
                                class="btn btn-info pull-right add-non-price"><span class="glyphicon glyphicon-plus"
                                                                                    aria-hidden="true"></span> {{Lang::get('keys.add_non_price')}}
                        </button>
                    </div>
                </div>
            @endif

            <hr>
            <h3>Лоти</h3>
            <br>

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
            @if ($procedureType->procurement_method != 'selective')
                <div class="form-group">
                    <div class="col-md-12">
                        <button data-lot="{{$lotIndex}}" data-proc="{{$procedureType->id}}" data-template="{{$template}}" data-organization="{{$organization->id}}" type="button"
                                class="btn btn-info pull-right add-lot-section"><span
                                    class="glyphicon glyphicon-plus" aria-hidden="true"></span> {{Lang::get('keys.add_lot')}}
                        </button>
                    </div>
                </div>
            @endif

            <script>
                $(function () {
                    $('#procedure_types_id').change(function () {
                        document.location.href = '/tender/create/' + $(this).val();
                    })
                });
                $(document).ready(function(){
                    $(window).on('resize',function(){
                        var winWidth =  $(window).width();
                        if(winWidth < 768 ){
                            console.log('Window Width: '+ winWidth + 'class used: col-xs');
                        }else if( winWidth <= 991){
                            console.log('Window Width: '+ winWidth + 'class used: col-sm');
                        }else if( winWidth <= 1199){
                            console.log('Window Width: '+ winWidth + 'class used: col-md');
                        }else{
                            console.log('Window Width: '+ winWidth + 'class used: col-lg');
                        }
                    });
                });
            </script>




