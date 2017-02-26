<div class="item-container item-section">
    <div class="col-md-12 text-left">
        <button type="button" class="close close-it accept-empty" style="float: left" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
    </div>
    {!! Form::hidden("items[$index][id]",null)  !!}
    <div class="form-group">
        <label for="items[{{$index}}][description]" class="col-md-4 control-label">Назва номенклатури</label>

        <div class="col-md-4">
            {!! Form::text("items[$index][description]",null, ['class' => 'form-control', 'placeholder' =>'', 'required'])  !!}
        </div>
        <div class="col-md-2">
            {!! Form::text("items[$index][quantity]",null, ['class' => 'form-control', 'placeholder' =>'к-ть', 'required'])  !!}
        </div>
        <div class="col-md-2">
            {!! Form::select("items[$index][unit_id]",$units,null,
            ['class' => 'form-control','required'
            ]) !!}
        </div>
    </div>

    @if (isset($item) && $item->codes->count() > 0)
        @foreach($item->codes as $ci => $code)

            <div class="form-group">
                @if ($code->type == 1)
                    <label for="items[{{$index}}][{{$code->classifier->alias}}]" class="col-md-4 control-label">Класифікатор {{$code->classifier->name}}</label>
                @else
                    <div class="col-md-4">
                        {!! Form::select(null, $classifiers, $code->type,
                        ['class' => 'form-control classifier-selector', 'placeholder' =>''])  !!}
                    </div>
                @endif


                <div class="col-md-8">
                    <?php $alias = $code->classifier->alias; ?>
                    {!! Form::text("items[$index][$alias]",isset($item->codes[$ci]) ? $item->codes[$ci]->code.' '.$item->codes[$ci]->description : null, ['class' => "form-control $alias", 'placeholder' =>''])  !!}
                    {!! Form::hidden("items[$index][codes][" . $code->type . "][id]",$code->id, ['class' => 'form-control', 'placeholder' =>''])  !!}
                </div>
            </div>
        @endforeach
    @else

        <div class="form-group">
            <label for="items[{{$index}}][cpv]" class="col-md-4 control-label">Класифікатор
                ДК 021:2015</label>

            <div class="col-md-8">
                {!! Form::text("items[$index][cpv]", null, ['class' => 'form-control cpv', 'placeholder' =>'', 'required'])  !!}
                {!! Form::hidden("items[$index][codes][1][id]",null, ['class' => 'form-control', 'placeholder' =>''])  !!}
            </div>
        </div>


        <?php if (isset($item) && isset($item->planning))
            $plan = $item->planning;?>
        <div class="form-group additional-codes @if ((isset($plan) && $plan && $plan->hasOneClassifier()) || (!isset($item) && !isset($plan) && time() > strtotime(env('ONE_CLASSIFIER_FROM')))) classifier_additional hidden @endif ">
            <div class="col-md-4">

                {!! Form::select(null, $classifiers, 2,
                ['class' => 'form-control classifier-selector', 'placeholder' =>'', 'style="width:84%;"'])  !!}
            </div>

            <div class="col-md-8">
                {!! Form::text("items[$index][dkpp]", null, ['class' => 'form-control  classifier', 'placeholder' =>''])  !!}
                {!! Form::hidden("items[$index][codes][0][id]",null, ['class' => 'form-control classifier2', 'placeholder' =>''])  !!}
            </div>
        </div>


    @endif

</div>
<hr>