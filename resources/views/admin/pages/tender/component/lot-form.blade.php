{!! Form::hidden("items[$index][id]",null)  !!}
<div class="item-section">
    <input type="hidden" class="index" value="{{$index}}">

    <div class="form-group">
        <label for="items[{{$index}}][description]" class="col-md-4 control-label">Загальні відомості про закупівлю</label>

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
            <label for="items[{{$index}}][{{$code->classifier->alias}}]" class="col-md-4 control-label">Класифікатор {{$code->classifier->name}}</label>

            <div class="col-md-8">
                <?php $alias = $code->classifier->alias; ?>
                {!! Form::text("items[$index][$alias]",isset($item->codes[$ci]) ? $item->codes[$ci]->code.' '.$item->codes[$ci]->description : null, ['class' => "form-control $alias", 'placeholder' =>'', 'required'])  !!}
                {!! Form::hidden("items[$index][codes][$ci][id]",null, ['class' => 'form-control', 'placeholder' =>''])  !!}
            </div>
        </div>
        @endforeach
    @else
        <div class="form-group">
            <label for="items[{{$index}}][dkpp]" class="col-md-4 control-label">Класифікатор ДКПП</label>

            <div class="col-md-8">
                {!! Form::text("items[$index][dkpp]", null, ['class' => 'form-control dkpp', 'placeholder' =>'', 'required'])  !!}
                {!! Form::hidden("items[$index][codes][0][id]",null, ['class' => 'form-control', 'placeholder' =>''])  !!}
            </div>
        </div>
        <div class="form-group">
            <label for="items[{{$index}}][cpv]" class="col-md-4 control-label">Класифікатор CPV</label>

            <div class="col-md-8">
                {!! Form::text("items[$index][cpv]", null, ['class' => 'form-control cpv', 'placeholder' =>'', 'required'])  !!}
                {!! Form::hidden("items[$index][codes][1][id]",null, ['class' => 'form-control', 'placeholder' =>''])  !!}
            </div>
        </div>
    @endif

    <div class="form-group">
        <label for="" class="col-md-4 control-label">Період поставки</label>

        <div class="col-md-4">
            <div class='input-group date'>
            {!! Form::text("items[$index][delivery_date_start]",null,['class' => 'form-control', 'placeholder' =>'З дд/мм/рррр',
            'date-time-picker',
                'pattern'=>'\d{2}\.\d{2}\.\d{4} \d{2}:\d{2}',
            'required'
            ]) !!}
                <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
            </div>
        </div>
        <div class="col-md-4">
            <div class='input-group date'>
            {!! Form::text("items[$index][delivery_date_end]",null,['class' => 'form-control', 'placeholder' =>'По дд/мм/гггг',
            'date-time-picker',
                'pattern'=>'\d{2}\.\d{2}\.\d{4} \d{2}:\d{2}',
            'required'
            ]) !!}
                <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                </div>
        </div>
    </div>

    <div class="form-group">
        <label for="select_address" class="col-md-4 control-label">Адреса поставки</label>

        <div class="col-md-8">
            <label for="same-{{$index}}" class="checkbox-inline">
                <input id="same-{{$index}}" type="radio" value="1" name="items[{{$index}}][same_delivery_address]" class="address-toggle-control same"
                        @if (!isset($item) || $item->same_delivery_address == 1) checked @endif>
                За адресою організації
            </label>
            <label for="origin-{{$index}}" class="checkbox-inline">
                <input type="radio" id="origin-{{$index}}" value="0" name="items[{{$index}}][same_delivery_address]" class="address-toggle-control origin"
                @if (isset($item) && $item->same_delivery_address == 0) checked @endif>
                За іншою адресою
            </label>
        </div>
    </div>

    <div class="delivery-address @if (isset($item) && $item->same_delivery_address == 0) show-address @endif">
        <div class="form-group">
            <label for="items[{{$index}}][country_id]" class="col-md-4 control-label">Країна</label>

            <div class="col-md-4">
                {!! Form::select("items[$index][country_id]",[1 => 'Україна'],1,
                ['class' => 'form-control','readonly','disabled'
                ]) !!}
            </div>
        </div>
        <div class="form-group">
            <label for="items[{{$index}}][region_id]" class="col-md-4 control-label">Область</label>

            <div class="col-md-4">
                {!! Form::select("items[$index][region_id]",$regions, empty($item->region_id) ? 'Оберіть область...' : null,
                ['class' => 'form-control'                ]) !!}
            </div>
        </div>
        <div class="form-group">
            <label for="items[{{$index}}][postal_code]" class="col-md-4 control-label">Поштовий індекс</label>

            <div class="col-md-4">
                {!! Form::text("items[$index][postal_code]", null, ['class' => 'form-control', 'placeholder' => ''])  !!}
            </div>
        </div>

        <div class="form-group">
            <label for="items[{{$index}}][locality]" class="col-md-4 control-label">Населений пункт</label>

            <div class="col-md-4">
                {!! Form::text("items[$index][locality]", null, ['class' => 'form-control', 'placeholder' => ''])  !!}
            </div>
        </div>

        <div class="form-group">
            <label for="items[{{$index}}][delivery_address]" class="col-md-4 control-label">Поштова адреса</label>

            <div class="col-md-4">
                {!! Form::text("items[$index][delivery_address]", null, ['class' => 'form-control', 'placeholder' => ''])  !!}
            </div>
        </div>
    </div>
    <hr>
</div>