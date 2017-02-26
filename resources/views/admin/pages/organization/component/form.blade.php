<div class="form-group">
    <label for="type" class="col-sm-4 control-label">Тип организації</label>
    <div class="col-lg-4">
        {!! Form::select('type',[1 => 'Фізична особа', 2 => 'Юридична особа'],'',
        ['class' => 'form-control','required'
        ]) !!}
    </div>
</div>
<div class="form-group">
    <label for="type" class="col-sm-4 control-label">Назва організації</label>
    <div class="col-lg-4">
        {!! Form::text('name',null,
        ['id'=>'inputName', 'class' => 'form-control', 'placeholder' =>'',
        'required'
        ]) !!}
    </div>
</div>

<div class="form-group">
    <label for="type" class="col-sm-4 control-label">Код ЄДРПОУ</label>
    <div class="col-lg-4">
        {!! Form::text('identifier',null,
        ['id'=>'inputIdentifier', 'class' => 'form-control', 'placeholder' =>'',
        'required',
        ]) !!}
    </div>
</div>

<hr>
<h3>Поштова адреса</h3>
<br>

<div class="form-group">
    <label for="country_id" class="col-sm-4 control-label">Країна</label>
    <div class="col-lg-4">
        {!! Form::select('',[1 => 'Україна'],'',
        ['class' => 'form-control','required','readonly','disabled'
        ]) !!}
        <input type="hidden" name="country_id" value="1">
    </div>
</div>

<div class="form-group">
    <label for="region_id" class="col-sm-4 control-label">Область</label>
    <div class="col-lg-4 ">
        {!! Form::select('region_id',$regions,null,
        ['class' => 'form-control','required'
        ]) !!}
    </div>
</div>
<div class="form-group">
    <label for="postal_code" class="col-sm-4 control-label">Поштовий індекс</label>
    <div class="col-lg-4">
        {!! Form::text('postal_code', null, ['class' => 'form-control', 'placeholder' => ''])  !!}
    </div>
</div>
<div class="form-group">
    <label for="locality" class="col-sm-4 control-label">Населений пункт</label>
    <div class="col-lg-4">
        {!! Form::text('locality', null, ['class' => 'form-control', 'placeholder' => '', 'required'])  !!}
    </div>
</div>

<div class="form-group">
    <label for="street_address" class="col-sm-4 control-label">Поштова адреса</label>
    <div class="col-lg-4">
        {!! Form::text('street_address', null, ['class' => 'form-control', 'placeholder' => '', 'required'])  !!}
    </div>
</div>

<hr>
<h3>Контактні дані</h3>
<br>

<div class="form-group">
    <label for="contact_name" class="col-sm-4 control-label">Контактна особа</label>
    <div class="col-lg-4">
        {!! Form::text('contact_name',null, ['class' => 'form-control', 'required']) !!}
    </div>
</div>

<div class="form-group">
    <label for="contact_phone" class="col-sm-4 control-label">Телефон</label>
    <div class="col-lg-4">
        {!! Form::text('contact_phone',null, ['class' => 'form-control', 'required']) !!}
    </div>
</div>

<div class="form-group">
    <label for="contact_email" class="col-sm-4 control-label">Email</label>
    <div class="col-lg-4">
        {!! Form::text('contact_email',null, ['class' => 'form-control']) !!}
    </div>
</div>


<div class="form-group">
    <label for="contact_url" class="col-sm-4 control-label">Сайт</label>
    <div class="col-lg-4">
        {!! Form::text('contact_url',null, ['class' => 'form-control', '']) !!}
    </div>
</div>


