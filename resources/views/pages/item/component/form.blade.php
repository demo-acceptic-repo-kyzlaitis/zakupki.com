<div class="form-group">
{{--    {!! Form::label('inputDescription','ОПИС',['class'=>'col-lg-3 control-label']) !!}--}}
    <div class="col-lg-9 col-sm-offset-3">
        {!! Form::textArea('description',null,
           ['id'=>'inputDescription', 'class' => 'form-control', 'placeholder' =>'ОПИС',
            'rows' =>'3',
            'required'
           ]) !!}
    </div>
</div>

<div class="form-group">
{{--    {!! Form::label('inputUnit','ОДИНИЦЯ ВИМІРУ',['class'=>'col-lg-3 control-label']) !!}--}}
    <div class="col-lg-6 col-sm-offset-3">
        {!! Form::text('unit',null,
           ['id'=>'inputUnit', 'class' => 'form-control', 'placeholder' =>'ОДИНИЦЯ ВИМІРУ',
            'required',
           ]) !!}
    </div>
    {{--    {!! Form::label('inputQuantity','КІЛЬКІСТЬ',['class'=>'col-lg-3 control-label']) !!}--}}
    <div class="col-lg-3">
        {!! Form::text('quantity',null,
            ['id'=>'inputQuantity','class' => 'form-control', 'placeholder' =>'КІЛЬКІСТЬ',
             'required'
            ]) !!}
    </div>

</div>

<fieldset class="sub-legend">
    <legend>ПЕРІОД ДОСТАВКИ</legend>
    {{-- Delivery Date START --}}
    <div class="form-group">
{{--        {!! Form::label('inputDeliveryStartDate','ПОЧАТОК',['class'=>'col-lg-2 col-sm-offset-2 control-label']) !!}--}}
        <div class="col-lg-3 col-sm-offset-3">
            {!! Form::text('delivery_start_date',null,
                ['id'=>'inputDeliveryStartDate', 'class' => 'form-control', 'placeholder' =>'С       дд/мм/рррр',
                 'data-mask' => '00/00/0000',
                 'pattern'=>'\d{2}/\d{2}/\d{4}',
                 'required'
                ]) !!}
        </div>

{{--        {!! Form::label('inputEnquiryEndDate','КІНЕЦЬ',['class'=>'col-lg-2 control-label']) !!}--}}
        <div class="col-lg-3 col-sm-offset-3">
            {!! Form::text('delivery_end_date',null,
                ['id'=>'inputEnquiryEndDate', 'class' => 'form-control', 'placeholder' =>'До       дд/мм/рррр',
                 'data-mask' => '00/00/0000',
                 'pattern'=>'\d{2}/\d{2}/\d{4}',
                 'required'
                ]) !!}
        </div>
    </div>
    {{-- Delivery Date END --}}
</fieldset>

<div class="form-group">
{{--    {!! Form::label('inputEnquiryEndDate','АДРЕСА ДОСТАВКИ',['class'=>'col-lg-3 control-label']) !!}--}}
    <div class="col-lg-9 col-sm-offset-3">
        {!! Form::text('delivery_address',null,
            ['id'=>'inputEnquiryEndDate', 'class' => 'form-control', 'placeholder' =>'АДРЕСА ДОСТАВКИ',
             'required'
            ])  !!}
    </div>
</div>

<div class="form-group">
{{--    {!! Form::label('inputEnquiryEndDate','Географічні координати місця доставки',['class'=>'col-lg-3 control-label']) !!}--}}
    <div class="col-lg-9 col-sm-offset-3">
        {!! Form::text('delivery_location',null,
            ['id'=>'inputEnquiryEndDate', 'class' => 'form-control', 'placeholder' =>'Географічні координати місця доставки',
             'required'
            ]) !!}
    </div>
</div>


