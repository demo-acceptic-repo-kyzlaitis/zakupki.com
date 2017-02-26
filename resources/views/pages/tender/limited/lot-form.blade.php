<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="item-section well lot-container">
    {!! Form::hidden("lots[$index][id]",null)  !!}

    @if($procedureType->procurement_method_type == 'negotiation.quick' || $procedureType->procurement_method_type == 'negotiation')
        <div class="section-close">
            <span class="lot-number"></span>
            @if (!isset($tender) || $tender->status == 'draft')    <a class="btn btn-danger btn-xs close-it">&times</a>
            <div style="clear: both; float: none; margin-bottom: 10px"></div> @endif
        </div>
        <div class="form-group">
            <label for="title" class="col-md-4 control-label">Узагальнена назва лоту</label>

            <div class="col-md-8">
                {!! Form::text("lots[$index][title]",null, ['class' => 'form-control', 'placeholder' =>'', 'required'])  !!}
            </div>
        </div>

        <div class="form-group">
            <label for="title" class="col-md-4 control-label">Примітки</label>

            <div class="col-md-8">
                {!! Form::textarea("lots[$index][description]",null, ['class' => 'form-control', 'placeholder' =>'', 'required', 'rows'=>3,])  !!}
            </div>
        </div>

        <div class="budjet_amount">
            <div class="form-group">
                <label for="title" class="col-md-4 control-label">Очікувана вартість закупівлі</label>

                <div class="col-md-8">
                    {!! Form::number("lots[$index][amount]",null, ['class' => 'form-control', 'placeholder' =>'', 'required'])  !!}
                </div>
            </div>
        </div>

        <hr>
    @endif
    <div class="item-container-{{$index}}">



        <b>Інформація про номенклатуру</b>
        <hr>
        @if (Session::has('_old_input') && isset(Session::get('_old_input')['lots'][$index]['items']))
            @foreach(Session::get('_old_input')['lots'][$index]['items'] as $itemIndex => $oldData)
                <?php $itemsIndex = count(Session::get('_old_input')['lots'][$index]['items']) - 1;?>
                @include('pages.tender.'.$template.'.item-form', ['lotIndex' => $index, 'index' => $itemIndex,  'same_address_disabled' => '', 'original_address_disabled' => 'disabled'])
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
            <?php $itemsIndex = 0;?>
            @include('pages.tender.'.$template.'.item-form', ['lotIndex' => $index, 'index' => 0,  'same_address_disabled' => '', 'original_address_disabled' => 'disabled'])
        @endif


    </div>

    <div class="form-group">

        <input type="hidden" class="item-future-index" value="0">
        <div class="col-md-4">
            <a data-lot="{{$index}}" data-item="{{$itemsIndex}}" data-template="{{$template}}" data-proc="{{$procedureType->id}}" data-organization="{{$organization->id}}" class="btn btn-success add-item-section"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> {{Lang::get('keys.add_item')}}</a>
        </div>
        <div class="col-md-8"></div>
    </div>

</div>





