{!! Form::hidden('bid_id', isset($bid) ? $bid->id : null, ['class' => 'form-control', 'placeholder' => '', 'required'])  !!}
<?php $existQualification = ($entity->status == 'active' || $entity->status == 'unsuccessful') ? true : false;?>


<div class="form-group">
    <label for="status_{{$entity->id}}" class="col-md-4 control-label">Статус</label>

    <div class="col-md-4">
        @if($existQualification)
            {!! Form::select("status_disabled", ['0' => 'Не вибрано', 'active' => 'Допустити до аукціону', 'unsuccessful' => 'Відхилити пропозицію'], $entity->status,  ['class' => 'form-control bid_qualify_status', 'id' => 'status_' . $entity->id, 'disabled']) !!}
            <input type="hidden" name="status" value="cancelled">
        @else
            {!! Form::select("status", ['0' => 'Не вибрано', 'active' => 'Допустити до аукціону', 'unsuccessful' => 'Відхилити пропозицію'], $entity->status,  ['class' => 'form-control bid_qualify_status', 'id' => 'status_' . $entity->id]) !!}
        @endif
    </div>
</div>

@if($existQualification && $entity->documents->count() > 0)
    @foreach($entity->documents as $document)
        <div class="form-group">
            <label for="qualification_{{$document->documentTypes->document_type}}_{{$entity->id}}"
                   class="col-md-4 control-label">{{$document->documentTypes->lang_ua}}</label>

            <div class="col-md-4">
                @if (!empty($document->url))
                    <a class="doc-download" href="{{$document->url}}">{{$document->title}} </a>
                @else
                    <a class="doc-download" href="#">{{basename($document->path)}} </a> <span>(Документ завантажується до центральної бази даних)</span>
                @endif
            </div>
        </div>
    @endforeach
@else
    <div class="form-group">
        <label for="qualification_protocol_{{$entity->id}}" class="col-md-4 control-label">Протокол розляду</label>

        <div class="col-md-4">
            {!! Form::file("protocol", ['class' => 'form-control', 'id' => 'qualification_protocol_' . $entity->id])  !!}
        </div>
    </div>
@endif

<div class="form-group bid_qualify_field bid_active">
    <label for="qualification_self_qualified_{{$entity->id}}" class="col-md-4 control-label">Відповідає кваліфікаційним
        критеріям, встановленим замовником в тендерній документації</label>

    <div class="col-md-4">
        @if($existQualification)
            {!! Form::checkbox("qualification_self_qualified", null, $entity->qualified, ['class' => 'bid_checkbox', 'id' => 'qualification_self_qualified_' . $entity->id, 'disabled'])  !!}
        @else
            {!! Form::checkbox("qualification_self_qualified", null, $entity->qualified, ['class' => 'bid_checkbox', 'id' => 'qualification_self_qualified_' . $entity->id])  !!}
        @endif
    </div>
</div>
<div class="form-group bid_qualify_field bid_active">
    <label for="qualification_self_eligible_{{$entity->id}}" class="col-md-4 control-label">Відсутні підстави для
        відмови в участі згідно ст. 17 Закону України ”Про Публічні закупівлі”</label>

    <div class="col-md-4">
        @if($existQualification)
            {!! Form::checkbox("qualification_self_eligible", null, $entity->eligible, ['class' => 'bid_checkbox', 'id' => 'qualification_self_eligible_' . $entity->id, 'disabled'])  !!}
        @else
            {!! Form::checkbox("qualification_self_eligible", null, $entity->eligible, ['class' => 'bid_checkbox', 'id' => 'qualification_self_eligible_' . $entity->id])  !!}
        @endif
    </div>
</div>
<div class="form-group bid_qualify_field bid_unsuccessful">
    <label for="unsuccessful_title_{{$entity->id}}" class="col-md-4 control-label">Причина відхилення</label>
    <div class="col-md-4">
        @if($existQualification)
            {!! Form::select("unsuccessful_title[]", $groundsForRejections['titles'], json_decode($entity->unsuccessful_title),  ['class' => 'form-control bid_unsuccessful_titles', 'id' => 'unsuccessful_title_' . $entity->id, 'multiple', 'disabled']) !!}
        @else
            {!! Form::select("unsuccessful_title[]", $groundsForRejections['titles'], json_decode($entity->unsuccessful_title),  ['class' => 'form-control bid_unsuccessful_titles', 'id' => 'unsuccessful_title_' . $entity->id, 'multiple']) !!}
        @endif
    </div>
</div>
<div class="form-group bid_qualify_field bid_unsuccessful">
    <label for="unsuccessful_description_{{$entity->id}}" class="col-md-4 control-label">Підстава відхилення</label>

    <div class="col-md-4">
        @if($existQualification)
            {!! Form::textarea("unsuccessful_description", $entity->unsuccessful_description, ['class' => 'form-control unsuccessful_description', 'id' => 'unsuccessful_description_' . $entity->id, 'disabled'])  !!}
        @else
            {!! Form::textarea("unsuccessful_description", $entity->unsuccessful_description, ['class' => 'form-control unsuccessful_description', 'id' => 'unsuccessful_description_' . $entity->id])  !!}
        @endif
    </div>
</div>
<div class="form-group">
    <div class="col-lg-12">
        @if(!$existQualification)
            {!! Form::submit(Lang::get('keys.save'),['class'=>'btn btn-primary center-block']) !!}
        @else
            {!! Form::submit(Lang::get('keys.cancel_decision'),['class'=>'btn btn-primary center-block']) !!}
        @endif
    </div>
</div>

