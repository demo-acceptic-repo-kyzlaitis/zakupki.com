<div class="form-group">
    <label for="type" class="col-sm-4 control-label">Сумма</label>
    <div class="col-lg-4">
        {!! Form::text('amount',null,
        ['class' => 'form-control', 'placeholder' =>'', 'required']) !!}
    </div>
</div>
<div class="form-group">
    <label for="type" class="col-sm-4 control-label">Платежная система</label>
    <div class="col-lg-4">
        {!! Form::select('payment_service_id', $paySystems, null,
        ['class' => 'form-control','required']) !!}
    </div>
</div>

<div class="form-group">
    <label for="type" class="col-sm-4 control-label">Підстава</label>
    <div class="col-lg-4">
        {!! Form::text('comment',null,
        [ 'class' => 'form-control', 'placeholder' =>'','']) !!}
    </div>
</div>

