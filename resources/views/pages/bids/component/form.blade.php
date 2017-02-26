{!! Form::hidden('entity_id', isset($entity) ? $entity->id : null, ['class' => 'form-control', 'placeholder' => '', 'required'])  !!}
{!! Form::hidden('entity_type', isset($entity) ? $entity->type : null, ['class' => 'form-control', 'placeholder' => '', 'required'])  !!}
@if (isset($bid) && $bid->status == 'invalid')
    @if ($bid->tender->procedureType->procurement_method_type == 'aboveThresholdEU')
        {!! Form::hidden('status', 'pending', [])  !!}
@elseif($bid->tender->procedureType->procurement_method_type == 'aboveThresholdUA')
    {!! Form::hidden('status', 'active', [])  !!} @endif
@endif
<div class="form-group">
    <label class="col-md-4 control-label">Бюджет @if ($entity->type == 'tender') закупівлі @else лоту @endif</label>

    <label class="col-md-8 control-label" style="text-align: left">{{number_format($entity->amount, 2, '.', ' ')}} {{$tender->currency->currency_code}} @if ($tender->tax_included) (Враховуючи ПДВ) @endif</label>
    <input type="hidden" class="entity_amount" value="{{(int)$entity->amount}}">
</div>
@if ($tender->procedureType->procurement_method_type != 'competitiveDialogueUA' && $tender->procedureType->procurement_method_type != 'competitiveDialogueEU')
    <div class="form-group">
        <label for="amount" class="col-md-4 control-label">Загальна вартість пропозиції</label>

        <div class="col-md-2">
            @if ($entity->tender->status == 'active.qualification' || $entity->tender->status == 'active.awarded')

                {!! Form::number('amount', null, ['class' => 'form-control bid_amount', 'readonly', 'placeholder' => $entity->tender->currency->currency_description, 'step' => "0.01"])  !!}
            @else
                {!! Form::number('amount', null, ['class' => 'form-control bid_amount', 'placeholder' => $entity->tender->currency->currency_description, 'step' => "0.01"])  !!}

            @endif
        </div>
        <label class="col-md-3 control-label" style="text-align: left">{{$tender->currency->currency_code}} @if ($tender->tax_included) (Враховуючи ПДВ) @endif </label>
    </div>
@else
    {!! Form::hidden('amount', '0') !!}
@endif
@if (is_object($tender->procedureType) && ($tender->procedureType->procurement_method == 'open' || $tender->procedureType->procurement_method == 'selective') && $tender->procedureType->procurement_method_type != 'belowThreshold')
    <div class="form-group">
        <label for="self_qualified" class="col-md-4 control-label">Підтверджую відповідність квалифікаційним критеріям,
            встановленим замовником в тендерній документації</label>

        <div class="col-md-4">
            @if ($entity->tender->status == 'active.qualification' || isset($bid))
                {!! Form::checkbox('self_qualified', null, true, ['class' => 'bid_checkbox', 'readonly', 'disabled', 'id' => 'self_qualified'])  !!}
            @else
                {!! Form::checkbox('self_qualified', null, false, ['class' => 'bid_checkbox', 'id' => 'self_qualified'])  !!}
            @endif
        </div>
    </div>
    <div class="form-group">
        <label for="self_eligible" class="col-md-4 control-label">Підтверджую відсутність підстав для відмови в участі
            згідно
            статті 17 Закону України “Про публічні закупівлі”
        </label>

        <div class="col-md-4">
            @if ($entity->tender->status == 'active.qualification' || isset($bid))
                {!! Form::checkbox('self_eligible', null, true, ['class' => 'bid_checkbox', 'readonly', 'disabled', 'id' => 'self_eligible'])  !!}
            @else
                {!! Form::checkbox('self_eligible', null, false, ['class' => 'bid_checkbox', 'id' => 'self_eligible'])  !!}
            @endif
        </div>
    </div>
    <div class="form-group">
        <label for="subcontracting_details" class="col-md-4 control-label">Інформація про субпідрядника</label>

        <div class="col-md-4">
            @if ($entity->tender->status == 'active.qualification' || $entity->tender->status == 'active.awarded')
                {!! Form::textarea('subcontracting_details', null, ['class' => 'form-control', 'readonly', 'id' => 'subcontracting_details'])  !!}
            @else
                {!! Form::textarea('subcontracting_details', null, ['class' => 'form-control', 'id' => 'subcontracting_details'])  !!}
            @endif
        </div>
    </div>
@endif
@if ($features->count() > 0)
    <h4>Нецінові показники</h4>
    <script>
        _bids_values = <?php echo $allValues->toJson();?>;
    </script>
    @foreach($features as $index => $feature)
        <div class="form-group">
            <label for="amount" class="col-sm-4 control-label">{{$feature->title}}</label>
            <div class="col-md-4">
                @if ($entity->tender->status == 'active.qualification')

                    {!! Form::select("values[$index][id]", $feature->values()->lists('title', 'id')->prepend('', ''), null,  ['readonly','disabled', 'class' => 'form-control values-select', 'id' => 'feature-'.$feature->id, 'data-fid' => $feature->id]) !!}
                @else
                    {!! Form::select("values[$index][id]", $feature->values()->lists('title', 'id')->prepend('', ''), null,  ['class' => 'form-control values-select', 'id' => 'feature-'.$feature->id, 'data-fid' => $feature->id]) !!}
                @endif
            </div>
            <div class="col-md-1" id="feature-value-{{$feature->id}}">
                <div class="cont control-label"></div>
                <script>
                    var featureValue = '';
                    if (typeof _bids_values[$('#feature-{{$feature->id}}').val()] != "undefined") {
                        featureValue = _bids_values[$('#feature-{{$feature->id}}').val()];
                    }
                    $('#feature-value-{{$feature->id}} .cont').html('Вага: ' + featureValue + '%');
                </script>
            </div>
        </div>
    @endforeach
    <script>
        $('.values-select').on('change', function(){
            $('#feature-value-' + $(this).data('fid') + ' .cont').html('Вага: ' + _bids_values[$(this).val()] + '%');
        })
    </script>
@endif


@if (isset($bid) && $bid->documents->count() > 0)
    <div class="row">
        <div class="col-md-12">
            <h4>Документи</h4>
            @include('share.component.document-list', ['entity' => $bid, 'size' => 'file-icon-sm', 'delete' => true, 'edit' => true, 'route' => 'bid.docs.destroy'])
        </div>
    </div>
@endif
{{--section uploading file--}}
@if (isset($bid))
@include('share.component.add-file-component',['procedureType' => $tender->procedureType, 'documentTypes' => $documentTypes, 'index' => 1, 'namespace' => 'bid', 'inputName' => 'bid'])
@else
@include('share.component.add-file-component',['procedureType' => $tender->procedureType, 'documentTypes' => $documentTypes, 'index' => 1, 'namespace' => 'bid', 'inputName' => 'bid'])
@endif
{{--section uploading file--}}


@if ($tender->procedureType->procurement_method_type == 'aboveThresholdEU')
    <a data-href="#" href="#"
       data-toggle="modal" data-target="#alert"
       class="btn btn-success hidden" id="modal-alert">
    </a>
    @include('share.component.modal-alert', ['modalNamespace' => 'alert', 'modalTitle' => 'Шановний користувач', 'modalMessage' => 'Зверніть увагу, що даний тип документів стає доступним замовнику та іншим учасникам тільки <b>після завершення аукціону</b>.'])

    <a data-href="#" href="#"
       data-toggle="modal" data-target="#confidential"
       class="btn btn-success hidden" id="modal-alert-confidential">
    </a>
     @include('share.component.modal-alert', ['modalNamespace' => 'confidential', 'modalTitle' => 'Шановний користувач', 'modalMessage' => 'Зверніть увагу, що у випадку активації конфіденційності, документ буде доступний <b>тільки замовнику</b>'])

    <script type="text/javascript">
        $('body').on('change','select', function () {
            var str = $("option:selected",this).text();
            if (str == "Кошторис" || str == "Цінова пропозиція") {
                $('#modal-alert').click();
            }
        }).on();

        $('body').on('click','input[name="confidential"]',function(){
            if (this.checked) $('#modal-alert-confidential').click();
        });
    </script>


@endif

