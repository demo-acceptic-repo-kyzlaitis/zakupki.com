<hr>
<h3>Інформація</h3>
<br>
<div class="form-group">
    <label for="title" class="col-md-4 control-label">Назва</label>

    <div class="col-md-8">
        {!! Form::text('title',null, ['class' => 'form-control', 'placeholder' =>'', 'required'])  !!}
    </div>
</div>
<div class="form-group">
    <label for="description" class="col-md-4 control-label">Опис</label>

    <div class="col-md-8">
        {!! Form::textArea('description',null,['class' => 'form-control', 'placeholder' =>'',
        'rows' =>'3',
        'required'
        ])  !!}
    </div>
</div>


<div class="form-group">
    <label for="amount" class="col-md-4 control-label">Очікувана вартість</label>

    <div class="col-md-6">
        {!! Form::text('amount',null, ['class' => 'form-control', 'placeholder' =>'',
        'data-number-format',
        'required',
        ])  !!}
    </div>
    <div class="col-md-2">
        {!! Form::select('currency_id',$currencies,null,
        [ 'class' => 'form-control','required'
        ]) !!}
    </div>
</div>

<div class="form-group">

    <div class="col-md-8 col-md-offset-4">
        <div class="checkbox">
            <label>
                {{--{!! Form::checkbox('tax_included',1, (bool)$tender->tax_included, [ 'placeholder' =>''])  !!} Враховуючи ПДВ--}}
                {!! Form::checkbox('tax_included',1, false, [ 'placeholder' =>''])  !!} Враховуючи ПДВ
            </label>
        </div>
    </div>

</div>


<div class="form-group">
    <label for="minimal_step" class="col-md-4 control-label">Розмір мінімального кроку пониження ціни</label>

    <div class="col-md-8">
        {!! Form::text('minimal_step',null, ['class' => 'form-control', 'placeholder' =>'',
        'data-number-format',
        'required'
        ])  !!}
    </div>
</div>
<hr>
<h3>Дата</h3>
<br>
<div class="form-group">
    <label class="col-md-4 control-label">Період уточнень</label>

    <div class="col-md-4">
        <div class='input-group date'>
        {!! Form::text('enquiry_start_date',null,['class' => 'form-control', 'placeholder' =>'З дд/мм/рррр',
        'date-time-picker',
            'pattern'=>'\d{2}\.\d{2}\.\d{4} \d{2}:\d{2}',
            'locale' => 'ru',
        'required'
        ]) !!}
        <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
        </div>
    </div>
    <div class="col-md-4">
        <div class='input-group date'>
        {!! Form::text('enquiry_end_date',null,['class' => 'form-control', 'placeholder' =>'По дд/мм/рррр',
        'date-time-picker',
        'pattern'=>'\d{2}\.\d{2}\.\d{4} \d{2}:\d{2}',
        'required'
        ]) !!}<span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
            </div>
    </div>
</div>

<div class="form-group">
    <label class="col-md-4 control-label">Період прийому пропозицій</label>

    <div class="col-md-4">
        <div class='input-group date'>
        {!! Form::text('tender_start_date',null,['class' => 'form-control', 'placeholder' =>'З дд/мм/рррр',
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
        {!! Form::text('tender_end_date',null,['class' => 'form-control', 'placeholder' =>'По дд/мм/рррр',
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




<hr>
<h3>Конкретна назва предмету закупівлі</h3>
<br>

<div class="items-container">
    @if (Session::has('_old_input'))
        @foreach(Session::get('_old_input')['items'] as $index => $oldData)
            @include('pages.tender.'.$template.'.lot-form',compact($index))
        @endforeach
    @elseif (isset($tender) && $tender->items->count() > 0)
        @foreach($tender->items as $index => $item)
            @include('pages.tender.'.$template.'.lot-form',compact($index, $tender))
        @endforeach
    @else
        @include('pages.tender.'.$template.'.lot-form', ['index' => 0])
    @endif
</div>

<div class="form-group">
    <div class="col-md-12">
        <button type="button" class="btn btn-info pull-right add-item-section">{{Lang::get('keys.add_lot')}}</button>
    </div>
</div>


{{--section uploading file--}}
@include('share.component.add-file-component',compact('documentTypes'))
{{--section uploading file--}}
<table  class="table table-striped table-bordered">
    @if (isset($tender))
        @foreach($tender->documents as $document)
            <tr>

                <td>@if (!empty($document->url)) <a href="{{$document->url}}">{{$document->title}}</a> @else <a href="{{route('document.download', [$document->id])}}">{{basename($document->path)}}</a> @endif</td>
                <td>
                    <div href="#" class="fileUpload btn btn-danger btn-xs">
                        <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                        <input type="file" name="newfiles[{{$document->id}}]" class="upload" />
                    </div>
            </tr>
        @endforeach
    @endif
</table>

{{-- start section of dkpp modal window --}}
@include('pages.tender.'.$template.'.dkpp-modal-window')
{{-- start section of dkpp modal window --}}