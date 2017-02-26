{!! Form::hidden('entity_id', isset($entity) ? $entity->id : null, ['class' => 'form-control', 'placeholder' => '', 'required'])  !!}
{!! Form::hidden('entity_type', isset($entity) ? $entity->type : null, ['class' => 'form-control', 'placeholder' => '', 'required'])  !!}
<div class="form-group">
    <label class="col-md-4 control-label">Бюджет @if ($entity->type == 'tender') закупівлі @else лоту @endif</label>

    <label class="col-md-8 control-label" style="text-align: left">{{$entity->amount}} {{$tender->currency->currency_code}} @if ($tender->tax_included) (Враховуючи ПДВ) @endif</label>
</div>
    <div class="form-group">
        <label for="amount" class="col-md-4 control-label">Ціна</label>

        <div class="col-md-4">
            @if ($entity->tender->status == 'active.qualification')
                {!! Form::text('amount', null, ['class' => 'form-control', 'readonly', 'placeholder' => $entity->tender->currency->currency_description, ])  !!}
                @else
                {!! Form::text('amount', null, ['class' => 'form-control  bid-amount','readonly', 'placeholder' => $entity->tender->currency->currency_description, ])  !!}
                @endif
        </div>
    </div>
    @if (isset($bid) && $bid->documents->count() > 0)
        <div class="row">
            <div class="col-md-12">
                <h4>Документи</h4>
                @include('admin.pages.bids.document-list', ['entity' => $bid, 'size' => 'file-icon-sm', 'delete' => true, 'route' => 'bid.docs.destroy'])
            </div>
        </div>
    @else
        <div class="col-md-12 text-center">
            <h4>Учасник ще не додав жодного документа</h4>
        </div>
    @endif
    {{--section uploading file--}}

    {{--section uploading file--}}



