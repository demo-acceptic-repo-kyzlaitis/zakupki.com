<div class="feature-container">
    <div class="item-section">
        <hr>
        <?php
        if ($namespace == 'tender') {
            $prefix = 'features';
        } else {
            $prefix = $namespace . '[features]';
        }
        ?>
        {!! Form::hidden("{$prefix}[$index][id]",null)  !!}
        <div class="section-close">
            <a class="btn btn-danger btn-xs delelete-features close-it" feature-id="{{$index}}">×</a><div style="clear: both; float: none; margin-bottom: 10px"></div>
        </div>
        <div class="form-group">
            <label for="title" class="col-md-4 control-label">Назва показника</label>
            <div class="col-md-8">
                {!! Form::text("{$prefix}[$index][title]",null, ['class' => 'form-control', 'placeholder' =>'', 'required', $readonly])  !!}
            </div>
        </div>
        @if(is_object($procedureType) && ($procedureType->procurement_method_type == 'aboveThresholdEU' || $procedureType->procurement_method_type == 'aboveThresholdUA.defense' || $procedureType->procurement_method_type == 'competitiveDialogueEU.stage2' || $procedureType->procurement_method_type == 'competitiveDialogueEU'))
            <div class="form-group">
                <label for="title" class="col-md-4 control-label">Назва показника англ.</label>
                <div class="col-md-8">
                    {!! Form::text("{$prefix}[$index][title_en]",null, ['class' => 'form-control', 'placeholder' =>'', 'required', $readonly])  !!}
                </div>
            </div>
        @endif
        <div class="form-group">
            <label for="description" class="col-md-4 control-label">Коментар</label>
            <div class="col-md-8">
                {!! Form::text("{$prefix}[$index][description]",null, ['class' => 'form-control', 'placeholder' =>'', 'required', $readonly])  !!}
            </div>

        </div>

        <?php $container = str_replace([']', '['], [''], $namespace) . $index; ?>

        <div class="feature-values-container-{{$container}}">
            @if (Session::has('_old_input') && isset($feature['values']))
                @foreach($feature['values'] as $vIndex => $oldData)
                    <?php $valueIndex = count($feature['values']) - 1;?>
                    @include('pages.tender.'.$template.'.feature-value-form', ['namespace' => $namespace, 'featureIndex' => $index, 'index' => $vIndex])
                @endforeach
            @elseif (isset($feature) && $feature->values->count() > 0)
                <?php $valueIndex = $feature->values->count() - 1; ?>
                @foreach($feature->values as $vIndex => $value)
                    @include('pages.tender.'.$template.'.feature-value-form', ['namespace' => $namespace, 'featureIndex' => $index, 'index' => $vIndex, 'tender' => $tender])
                @endforeach
            @else
                <?php $valueIndex = 0;?>
                @include('pages.tender.'.$template.'.feature-value-form', ['namespace' => $namespace, 'featureIndex' => $index, 'index' => 0])
            @endif

        </div>
        @if ($procedureType->procurement_method != 'selective')
        <div class="form-group">
            <div class="col-md-12">

                <button data-namespace="{{$namespace}}" data-container="feature-values-container-{{$container}}" data-feature="{{$index}}" data-value="{{$valueIndex}}" data-proc="{{$procedureType->id}}" type="button" class="btn btn-success pull-right add-option"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Додати опцію</button>
            </div>
        </div>
        @endif
    </div>
</div>




