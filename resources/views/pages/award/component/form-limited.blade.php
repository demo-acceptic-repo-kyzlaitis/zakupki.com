<?php
    //определение схемы для вьюхи создания и для редактирования
    $countryScheme = $schemes['AU']; // default value
    if(isset($award) && isset($award->organization)) {
        $country = \App\Model\Country::find($award->organization->country_id);
        if($country){
            $countryScheme = $schemes[$country->country_iso];
        }
    }


?>

<input type="hidden" value="{{$tender->id}}" name="tender_id">
<div class="form-group guest">
    <label for="type" class="col-sm-4 control-label">Найменування учасника (для юридичної особи) або прізвище, ім’я, по батькові (для
        фізичної особи) </label>
    <div class="col-lg-4">
        {!! Form::text('organization[name]',null,['id'=>'inputName', 'class' => 'form-control', ]) !!}
    </div>
</div>

<div class="form-group scheme-div">
    <label for="type" class="col-sm-4 control-label">Cхема Ідентифікації</label>
    <div class="col-lg-4">
        {!! Form::select('organization[scheme]', $countryScheme, null, ['class' => 'form-control', 'id'=>'scheme-limited']) !!}
    </div>
</div>

<div class="form-group guest">
    <label for="type" class="col-sm-4 control-label identity-label">Код за ЄДРПОУ або реєстраційний номер облікової картки платника податків </label>
    <div class="col-lg-4">
        {!! Form::text('organization[identifier]',(isset($award) && isset($award->organization) ) ? $award->organization->identifier : null, ['id'=>'inputIdentifier', 'class' => 'form-control', 'placeholder' =>'','required']) !!}
    </div>
</div>

@if ($tender->procedureType->procurement_method == 'limited' && $tender->procedureType->procurement_method_type != 'reporting')
<input type="hidden" name="qualified" value="1">
<input type="hidden" name="subcontracting_details" value="">
    {{--<div class="form-group">--}}
        {{--<label for="self_qualified" class="col-md-4 control-label">Підтверджую відповідність квалифікаційним критеріям</label>--}}

        {{--<div class="col-lg-4">--}}
            {{--{!! Form::select('qualified',['1' => 'Підтверджую', '0' => 'Не підтверджую'], null,--}}
            {{--['class' => 'form-control','required',--}}
            {{--]) !!}--}}
        {{--</div>--}}

    {{--</div>--}}

    {{--<div class="form-group">--}}
        {{--<label for="subcontracting_details" class="col-md-4 control-label">Інформація про субпідрядника</label>--}}

        {{--<div class="col-md-4">--}}
            {{--{!! Form::textarea('subcontracting_details', null, ['class' => 'form-control', 'placeholder' => ''])  !!}--}}
        {{--</div>--}}
    {{--</div>--}}

@endif

<div class="address-guest-wrapper">
    <hr >
    <h4>Місцезнаходження</h4>
    <br>

    <div class="form-group">
        <label for="country_id" class="col-sm-4 control-label">Країна</label>
        <div class="col-lg-4">
            {{--is type id  = 4 5 6 --}}
            @if ($tender->isForNonResident())
                {!! Form::select('organization[country_id]', $countries, $country_id,
                ['class' => 'form-control nonresident_country','required',
                ]) !!}
                <input type="hidden" name="nonresident_country" value="1">
            @else
                {!! Form::select('',[1 => 'Україна'],'',
                ['class' => 'form-control','required','readonly','disabled',
                ]) !!}
                <input type="hidden" name="country_id" value="1">
            @endif
        </div>
    </div>

    <div class="form-group region-div">
        <label for="region_id" class="col-sm-4 control-label">Регіон</label>
        <div class="col-lg-4 ">
                {!! Form::select('organization[region_id]',$regions,$region_id,
                ['class' => 'form-control','required'
                ]) !!}
                {!! Form::text('organization[region_name]',null,
	            ['class' => 'form-control hidden','', 'placeholder' => ''
	            ]) !!}
           </div>
    </div>
    <div class="form-group">
        <label for="postal_code" class="col-sm-4 control-label">Поштовий індекс</label>
        <div class="col-lg-4">
            {!! Form::text('organization[postal_code]', null, ['class' => 'form-control', 'placeholder' => '', 'required'])  !!}
        </div>
    </div>
    <div class="form-group">
        <label for="locality" class="col-sm-4 control-label">Населений пункт</label>
        <div class="col-lg-4">
            {!! Form::text('organization[locality]', null, ['class' => 'form-control', 'placeholder' => '', 'required'])  !!}
        </div>
    </div>

    <div class="form-group">
        <label for="street_address" class="col-sm-4 control-label">Поштова адреса</label>
        <div class="col-lg-4">
            {!! Form::text('organization[street_address]', null, ['class' => 'form-control', 'placeholder' => '', 'required'])  !!}
        </div>
    </div>

</div>

<hr>
<h4>Контактні дані</h4>
<br>

<div class="form-group">
    <label for="contact_name" class="col-sm-4 control-label">Контактна особа</label>
    <div class="col-lg-4">
        {!! Form::text('organization[contact_name]',null, ['class' => 'form-control', 'required', 'placeholder' => 'Прізвище, ім\'я та по батькові']) !!}
    </div>
</div>

<div class="form-group">
    <label for="contact_phone" class="col-sm-4 control-label">Телефон</label>
    <div class="col-lg-4">
        {!! Form::text('organization[contact_phone]',null, ['class' => 'form-control', 'required', 'placeholder' => '+380930000000']) !!}
    </div>
</div>

<div class="form-group">
    <label for="contact_email" class="col-sm-4 control-label">Email</label>
    <div class="col-lg-4">
        {!! Form::text('organization[contact_email]',null, ['class' => 'form-control', 'placeholder' => '']) !!}
    </div>
</div>

<hr>
<h4>Сума</h4>
<br>
<div class="budjet_amount">
    <div class="form-group">
        <label for="amount" class="col-md-4 control-label">Ціна пропозиції</label>

        <div class="col-md-4">
            {!! Form::text("amount",null, ['class' => 'form-control budjet', 'id' => 'proposition_amount', 'data-tender-amount' => $tender->amount])  !!}
        </div>
    </div>

</div>

<hr>
<h4>Документи рішення</h4>
<br>

<div>
    @if (isset($award) && $award->documents()->count())
        @include('share.component.document-list', ['entity' => $award, 'size' => 'file-icon-sm'])
    @endif
    {{--section uploading file--}}
    @include('share.component.add-file-component',['documentTypes' => [], 'index' => '1', 'namespace' => "award-0", 'inputName' => "award[0]"])
    {{--section uploading file--}}
</div>


    {{--@if ($award->bid->documents()->count())--}}
        {{--<tr>--}}
            {{--<th>Документи пропозиції</th>--}}
            {{--<td>@include('share.below.document-list', ['entity' => $award->bid, 'size' => 'file-icon-sm'])</td>--}}
        {{--</tr>--}}
    {{--@endif--}}
    {{--@if ($award->documents()->count())--}}
        {{--@include('share.below.document-list', ['entity' => $award, 'size' => 'file-icon-sm'])--}}
    {{--@endif--}}
    {{--section uploading file--}}
    {{--@include('share.below.add-file-below',['documentTypes' => [], 'index' => '1', 'namespace' => "award-$index", 'inputName' => "award[$index]"])--}}
    {{--section uploading file--}}

<script type="text/javascript">
    var schemes = <?php echo json_encode($schemes); ?>;


    $(document).ready(function () {

        $('body').on('change', '.nonresident_country', (function () {

            var countryIso = $(this).val();

            var schemesSelectElement = $('#scheme-limited');
            schemesSelectElement.empty(); //delete all options from select
            $.each(schemes[countryIso], function (key, value) {
                schemesSelectElement.append($("<option></option>")
                        .attr("value", value).text(value));
            });


            if ($(this).val() == 'UA') {
                $('.identity-label').text('Код за ЄДРПОУ або реєстраційний номер облікової картки платника податків');
                //$('.region-div').show();

                $('.scheme-div').hide();
                console.log($(this).val());
                $('select[name="organization[region_id]"]').removeClass('hidden');
                $('input[name="organization[region_name]"]').addClass('hidden');
            } else {
                $('.scheme-div').show('fast');
                $('.identity-label').text('Ідентифікатор');
                $('select[name="organization[region_id]"]').addClass('hidden');
                $('input[name="organization[region_name]"]').removeClass('hidden');
                $('input[name="organization[region_name]"]').val('');
                console.log($(this).val());
            }
        }));

        if ($('.nonresident_country').val() != 'UA') {
            $('.identity-label').text('Ідентифікатор');
            $('select[name="organization[region_id]"]').addClass('hidden');
            $('input[name="organization[region_name]"]').removeClass('hidden');
        } else {
            $('.scheme-div').hide();
        }


    });
</script>
