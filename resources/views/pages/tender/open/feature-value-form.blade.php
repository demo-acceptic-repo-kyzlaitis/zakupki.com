<div class="form-group value-container">
    <?php
    if ($namespace == 'tender') {
        $prefix = 'features';
    } else {
        $prefix = $namespace . '[features]';
    }
    ?>
    {!! Form::hidden("{$prefix}[$featureIndex][values][$index][id]",null)  !!}
    <div class="col-md-2"></div>
    <label for="" class="col-md-2 control-label">Назва опції</label>
    <div class="col-md-3">
        {!! Form::text("{$prefix}[$featureIndex][values][$index][title]",null, ['class' => 'form-control', 'placeholder' =>'', 'required', $readonly])  !!}
    </div>
    <label for="" class="col-md-2 control-label">Вага нецінового критерію %</label>

    <div class="col-md-2">
        {!! Form::number("{$prefix}[$featureIndex][values][$index][value]",null, ['class' => 'form-control','pattern'=>"[0..9]{,2}+",'type'=>'number','min'=>'0', 'placeholder' =>'0', 'required', $readonly])  !!}

    </div>
    @if ($procedureType->procurement_method != 'selective')
    <div class="col-md-1"><button type="button" class="btn btn-danger pull-right del-option">-</button></div>
    @endif
</div>