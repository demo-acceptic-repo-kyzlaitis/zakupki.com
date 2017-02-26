<hr>
<br>
<div class="form-group">
    <label for="type_id" class="col-md-4 control-label">Тип процедури</label>

    <div class="col-md-8">
        {!! Form::select("procedure_id",$procedureTypes,null,
          [ 'class' => 'form-control','required'
        ]) !!}
    </div>
</div>

<div class="form-group">
    <label for="title" class="col-md-4 control-label">Рік</label>

    <div class="col-md-8">
        {!! Form::text('year',null, ['class' => 'form-control', 'placeholder' =>'', 'required'])  !!}
    </div>
</div>

<div class="form-group">
    <label for="title" class="col-md-4 control-label">Конкретна назва предмета закупівлі</label>

    <div class="col-md-8">
        {!! Form::text('description',null, ['class' => 'form-control', 'placeholder' =>'', 'required'])  !!}
    </div>
</div>
<div class="form-group">
    <label for="notes" class="col-md-4 control-label">Примітки</label>

    <div class="col-md-8">
        {!! Form::textArea('notes',null,['class' => 'form-control', 'placeholder' =>'',
        'rows' =>'3',
        'required'
        ])  !!}
    </div>
</div>
<div class="budjet_amount">
    <div class="form-group">
        <label for="amount" class="col-md-4 control-label">Очікувана вартість предмета закупівлі</label>

        <div class="col-md-4">
            {!! Form::text("amount",null, ['class' => 'form-control budjet',
            ])  !!}
        </div>
    </div>
</div>
<div class="form-group">
    <label for="currency_id" class="col-md-4 control-label">Валюта</label>

    <div class="col-md-4">
        {!! Form::select("currency_id",$currencies,null,
        [ 'class' => 'form-control','required'
        ]) !!}
    </div>
</div>


<div class="form-group">
    <label for="reason" class="col-sm-4 control-label">Орієнтовний початок процедури закупівлі</label>

    <div class="col-md-2">
        {!! Form::select("start_day",$days,isset($plan->start_day) ? $plan->start_day : 0,
        [ 'class' => 'form-control' ]) !!}
    </div>

    <div class="col-md-2">
        {!! Form::select("start_month",$months,isset($plan->start_month) ? $plan->start_month : date('m'),
        [ 'class' => 'form-control' ]) !!}
    </div>

    <div class="col-md-2">
        {!! Form::select("start_year",$years,isset($plan->start_year) ? $plan->start_year : date('Y'),
        [ 'class' => 'form-control' ]) !!}
    </div>

</div>



    <div class="form-group">
        <label for="code_id" class="col-md-4 control-label">Класифікатор ДК 021:2015</label>

        <div class="col-md-8">
            {!! Form::text("code", isset($plan->code_id) ? $plan->code->code.' '.$plan->code->description : null, ['class' => 'form-control cpv', 'placeholder' =>'', 'required'])  !!}
            {!! Form::hidden("code_id",null, ['class' => 'form-control', 'placeholder' =>''])  !!}
        </div>
    </div>

    <div class="form-group additional-codes @if ((isset($plan) && $plan->hasOneClassifier()) || !isset($plan) && time() > strtotime(env('ONE_CLASSIFIER_FROM'))) classifier_additional hidden @endif ">
        <div class="col-md-4">

           {!! Form::select(null, $classifiers, 2,
            ['class' => 'form-control classifier-selector', 'placeholder' =>'',''])  !!}
        </div>

        <div class="col-md-8">
            {!! Form::text("code_additional", (isset($plan->code_additional_id) && $plan->code_additional_id) ? $plan->codeAdditional->code.' '.$plan->codeAdditional->description : null, ['class' => 'form-control  classifier', 'placeholder' =>''])  !!}
            {!! Form::hidden("code_additional_id",null, ['class' => 'form-control classifier2', 'placeholder' =>''])  !!}
        </div>
    </div>

<div class="form-group">
    <label for="code_id" class="col-md-4 control-label">Код КЕКВ</label>

    <div class="col-md-8">
        {!! Form::text("code_kekv", isset($plan->code_kekv_id) && $plan->code_kekv_id != 0 ? $plan->codeKekv->code.' '.$plan->codeKekv->description : null, ['class' => 'form-control kekv', 'placeholder' =>'', ''])  !!}
        {!! Form::hidden("code_kekv_id",null, ['class' => 'form-control', 'placeholder' =>''])  !!}
    </div>
</div>

<hr>
<h3>Номенклатура</h3>
<br>
<div class="items-container">
    @if (Session::has('_old_input') && isset(Session::get('_old_input')['items']))
        @foreach(Session::get('_old_input')['items'] as $index => $oldData)
            <?php $itemsIndex = count(Session::get('_old_input')['items']) - 1;?>
            @include('pages.planning.component.item-form', ['index' => $index])
        @endforeach
    @elseif (isset($plan) && $plan->items->count() > 0)
        <?php $itemsIndex = $plan->items->count() - 1; ?>
        @foreach($plan->items as $index => $item)
                @include('pages.planning.component.item-form', ['index' => $index])
        @endforeach
    @else
        <?php $itemsIndex = 0;?>
    @endif
</div>

<div class="form-group">

    <div class="col-md-4">
        <a data-item="{{$itemsIndex}}" class="btn btn-success add-item-section-plan" data-plan="@if(isset($plan)) {{$plan->id}} @else 0 @endif "><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> {{Lang::get('keys.add_item')}}</a>
    </div>
    <div class="col-md-8"></div>
</div>

{{--section uploading file--}}
{{--@if($isEditable)--}}
<hr>
<h3>Документація</h3>
@include('share.component.add-file-component',['index' => 1, 'namespace' => 'plan', 'inputName' => 'plan'])
{{--section uploading file--}}
{{--@endif--}}
<table class="table table-striped table-bordered">
    @if (isset($plan))
        @foreach($plan->documents as $document)
            <tr>
                <td>
                    @if (!empty($document->url))
                        <a href="{{$document->url}}">{{basename($document->path)}} </a>
                    @else
                        <a class="doc-download" href="javascript:void(0)">{{basename($document->path)}} </a> <span>(Документ завантажується до центральної бази даних)</span>
                    @endif
                </td>
            </tr>
        @endforeach
    @endif
</table>





