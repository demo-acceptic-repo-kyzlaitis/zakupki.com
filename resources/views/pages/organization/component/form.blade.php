<?php
    $arr = \App\Model\Kind::lists('name', 'id')->all();
    $role_type = array();
    $role_type[2] = $arr[2];
    $role_type[4] = $arr[4];
    $role_type[3] = $arr[3];
    $role_type[1] = $arr[1];
?>
@if (isset($admin) || !isset($organization))
<div class="form-group">
        <label for="type" class="col-sm-4 control-label">Роль організації</label>

        <div class="col-lg-4">
            {!! Form::select('type',['' => '', 'customer' => 'Замовник', 'supplier' => 'Учасник', 'guest' => 'Гість'], null,
            ['class' => 'form-control type-select','required'
            ]) !!}
        </div>
    </div>
    <div class="form-group show-kind"
         @if ((isset($organization) && $organization->type !== 'customer') || (old('type') === 'guest' || old('type') === 'supplier'))) @hide @endif >
        <label for="type" class="col-sm-4 control-label">Тип замовника</label>
        <div class="col-lg-4">
            <?php
            $attr = 'disabled';
            if (old('type') === 'customer' || old('type') === null) {
                $attr = 'required';
            }
            ?>
                <select class="form-control kind-select" required name="kind_id">

                    <?php
                    foreach($role_type as $key =>$type){?>
                    <option @if(isset($organization->kind_id) && $organization->kind_id  == $key) selected="selected"
                            @endif value="<?php echo $key?>"
                            @if($key == 3) title="Юридичні особи, які не є замовниками в розумінні Закону, але є державними, комунальними, казенними підприємствами,господарськими товариствами чи об'єднаннями підприємств,у яких державна чи комунальна частка складає 50 і більше відсотків." @endif><?php echo $type ?></option>
                    <?php } ?>
                </select>
        </div>
    </div>

@else
    <div class="form-group">
        <label for="type" class="col-sm-4 control-label">Роль організації</label>

        <div class="col-lg-4">
            {!! Form::select('type',['' => '', 'customer' => 'Замовник', 'supplier' => 'Учасник', 'guest' => 'Гість'], null,
            ['class' => 'form-control type-select','required','disabled'
            ]) !!}
        </div>
    </div>

    <div class="form-group show-kind"
         @if ((isset($organization) && $organization->type !== 'customer') || (old('type') === 'guest' || old('type') === 'supplier'))) @hide @endif >
        <label for="type" class="col-sm-4 control-label">Тип замовника</label>
        <div class="col-lg-4">
            <?php
            $attr = 'disabled';
            if (old('type') === 'customer' || old('type') === null) {
                $attr = 'required';
            }
            ?>
                <select class="form-control kind-select" required="required" name="kind_id" disabled>
                    <?php
                    foreach($role_type as $key =>$type){?>
                    <option @if(isset($organization->kind_id) && $organization->kind_id  == $key) selected="selected"
                            @endif value="<?php echo $key?>" @if($key == 1) disabled @elseif($key == 3) title="Юридичні особи, які не є замовниками в розумінні Закону, але є державними, комунальними, казенними підприємствами,господарськими товариствами чи об'єднаннями підприємств,у яких державна чи комунальна частка складає 50 і більше відсотків." @endif><?php echo $type ?></option>
                    <?php } ?>
                </select>
        </div>
    </div>
@endif

<div class="form-group guest" @if(old('type') === 'guest') @hide @endif>
    <label for="type" class="col-sm-4 control-label">Назва організації</label>

    <div class="col-lg-4">
        @if (old('type') && old('type') === 'guest')
            {!! Form::text('name',null,['id'=>'name', 'class' => 'form-control', 'disabled']) !!}
        @else
            {!! Form::text('name',null,['id'=>'name', 'class' => 'form-control', ]) !!}
        @endif
        {{--{!! Form::text('name',null,['id'=>'name', 'class' => 'form-control', ]) !!}--}}
        <div class="error-box"></div>
    </div>
</div>

<div class="form-group guest" @if(old('type') === 'guest') @hide @endif>
    <label for="type" class="col-sm-4 control-label">Повна юридична назва</label>

    <div class="col-lg-4">
        @if (old('type') && old('type') === 'guest')
            {!! Form::text('legal_name',null,['id'=>'Full_legal_name', 'class' => 'form-control', 'disabled']) !!}
        @else
            {!! Form::text('legal_name',null,['id'=>'Full_legal_name', 'class' => 'form-control', ]) !!}
        @endif
        {{--{!! Form::text('name',null,['id'=>'Full_legal_name', 'class' => 'form-control', ]) !!}--}}
         <div class="error-box"></div>
    </div>
</div>

<div class="form-group guest" @if(old('type') === 'guest') @hide @endif>
    <label for="type" class="col-sm-4 control-label">Повна юридична назва англійською мовою</label>
    <div class="col-lg-4">
        @if (old('type') && old('type') === 'guest')
            {!! Form::text('legal_name_en',null,['id'=>'Full_legal_name_eng', 'class' => 'form-control', 'disabled']) !!}
        @else
            {!! Form::text('legal_name_en',null,['id'=>'Full_legal_name_eng', 'class' => 'form-control', ]) !!}
        @endif
         <div class="error-box"></div>
    </div>
</div>

<div class="form-group country_scheme" @if(old('type') === 'guest' || !isset($organization)) @hide @endif>
    <label for="scheme" class="col-sm-4 control-label">Схема ідентифікації</label>

    <div class="col-lg-4">
    <?php //dd($organization->identifiers()->first()->scheme->country_iso);?>
        @if (old('type') && old('type') === 'guest')
            {!! Form::select('country_scheme', $schemes['UA'],null,
               ['class' => 'form-control','disabled']) !!}
        @else
            {!! Form::select('country_scheme', isset($organization) ? $schemes[$organization->identifiers()->first()->scheme->country_iso] : $schemes['UA'], null,['class' => 'form-control country_scheme', 'id'=>'country_scheme']) !!}
        @endif
    </div>
</div>

<div class="form-group guest" @if(old('type') === 'guest') @hide @endif>
    <label for="inputIdentifier" class="col-sm-4 control-label">Код за ЄДРПОУ або реєстраційний номер облікової картки платника податків</label>

    <div class="col-lg-4">
        @if (old('type') && old('type') === 'guest')
            {!! Form::text('organization_identifier',(isset($organization) ) ? $organization->identifier : null, ['id'=>'inputIdentifier', 'class' => 'form-control', 'placeholder' =>'','disabled']) !!}
        @else
            {!! Form::text('organization_identifier',(isset($organization) ) ? $organization->identifier : null, ['id'=>'inputIdentifier', 'class' => 'form-control', 'placeholder' =>'']) !!}
        @endif
        <div class="error-box"></div>
    </div>
</div>

<div class="address-guest-wrapper" @if(old('type') === 'guest') @hide @endif>
    <div class="guest">
        <hr>
        <h3>Поштова адреса</h3>
        <br>
    </div>

    <div class="form-group guest">
        <label for="country_id" class="col-sm-4 control-label">Країна</label>

        <div class="col-lg-4">
            {!! Form::select('country_iso',$countries,(isset($organization) ) ? $organization->country->country_iso : 'UA',
            ['class' => 'form-control','required', 'id' => 'country']) !!}
        </div>
    </div>

    <div class="form-group region guest">
        <label for="region_id" class="col-sm-4 control-label">Регіон</label>
		<div class="col-lg-4 ">
            @if (old('type') && old('type') === 'guest')
                @if ((isset($organization) && $organization->country->country_iso == 'UA') || (!isset($organization))) 
	                {!! Form::select('region_id',$regions,null,
	                ['class' => 'form-control','disabled','id'=>'region_id'
	                ]) !!}
	            	{!! Form::text('region_name',null,
	                ['class' => 'form-control hidden','disabled', 'placeholder' => '','id'=>'region_name'
	                ]) !!}
	            @else
	                {!! Form::select('region_id',$regions,null,
	                ['class' => 'form-control hidden','disabled','id'=>'region_id'
	                ]) !!}
	            	{!! Form::text('region_name',null,
	                ['class' => 'form-control ','disabled', 'placeholder' => '','id'=>'region_name'
	                ]) !!}	            
                @endif
            @else
                @if ((isset($organization) && $organization->country->country_iso == 'UA') || (!isset($organization))) 
	                {!! Form::select('region_id',$regions,(!isset($organization))?26:null,
	                ['class' => 'form-control','id'=>'region_id'
	                ]) !!}
	            	{!! Form::text('region_name',null,
	                ['class' => 'form-control hidden', 'placeholder' => '','id'=>'region_name'
	                ]) !!}
	            @else
	                {!! Form::select('region_id',$regions,null,
	                ['class' => 'form-control hidden','','id'=>'region_id'
	                ]) !!}
	            	{!! Form::text('region_name',null,
	                ['class' => 'form-control','', 'placeholder' => '','id'=>'region_name'
	                ]) !!}
                @endif
            @endif
        </div>
    </div>

    <div class="form-group guest">
        <label for="postal_code" class="col-sm-4 control-label">Поштовий індекс</label>

        <div class="col-lg-4">
            @if (old('type') && old('type') === 'guest')
                {!! Form::text('postal_code', null, ['class' => 'form-control', 'placeholder' => '', 'disabled'])  !!}
            @else
                {!! Form::text('postal_code', null, ['class' => 'form-control', 'id' => 'postalIndex', 'placeholder' => ''])  !!}
            @endif
            <div class="error-box"></div>
        </div>
    </div>

    <div class="form-group guest">
        <label for="locality" class="col-sm-4 control-label">Населений пункт</label>

        <div class="col-lg-4">
            @if (old('type') && old('type') === 'guest')
                {!! Form::text('locality', null, ['class' => 'form-control', 'placeholder' => '', 'disabled'])  !!}
            @else
                {!! Form::text('locality', null, ['class' => 'form-control', 'id' => 'locality', 'placeholder' => ''])  !!}
            @endif
            <div class="error-box"></div>
        </div>
    </div>

    <div class="form-group guest">
        <label for="street_address" class="col-sm-4 control-label">Поштова адреса</label>

        <div class="col-lg-4">
            @if (old('type') && old('type') === 'guest')
                {!! Form::text('street_address', null, ['class' => 'form-control', 'placeholder' => '', 'disabled'])  !!}
            @else
                {!! Form::text('street_address', null, ['class' => 'form-control', 'id' => 'postalAddress', 'placeholder' => ''])  !!}
            @endif
            <div class="error-box"></div>

        </div>
    </div>

</div>


<hr>
<h3>Контактні дані </h3>
<br>

<?php
if(isset($conts)){
    $allContacts = $conts;
}elseif(isset($contactsAll)){
    $allContacts = $contactsAll;
}
if (isset($allContacts)){

$i = 1;
foreach($allContacts as $cont) {

$id = $cont['id'];
?>

<div class="contacts" ordinal="<?php echo $i ?>">

    <div class="form-group">
        <label for="contact_email" class="col-sm-4 control-label">Основний Контакт</label>
        <div class="col-lg-4">
            <?php if ($cont['primary'] == 1) {
                ?><input type="radio" name="primary_contact" class="primary" checked="enabled" value="<?php echo $id ?>" /><?php
            } else {
                ?><input type="radio" name="primary_contact" class="primary" value="<?php echo $id ?>" /><?php

            }?>
        </div>
        <button type="button" class="close close-it-contact id" data-dismiss="modal" aria-label="Close" id="<?php echo $id ?>"><span aria-hidden="true">×</span></button>
    </div>

    <div class="form-group">
        <label for="contact_name" class="col-sm-4 control-label">Контактна особа</label>

        <div class="col-lg-4">
            {!! Form::text("contact[$id][contact_name]",$cont['contact_name'], ['class' => 'form-control contact_name', 'id' => 'contactPerson', 'required', 'placeholder' => 'Прізвище, ім\'я та по батькові']) !!}
            <div class="error-box"></div>
        </div>
    </div>

    <div class="form-group">
        <label for="contact_name" class="col-sm-4 control-label">Контактна особа англійською</label>

        <div class="col-lg-4">
            {!! Form::text("contact[$id][contact_name_en]",$cont['contact_name_en'], ['class' => 'form-control contact_name_en', 'id' => 'contactPersonEn', 'required', 'placeholder' => 'Name and Last Name']) !!}
              <div class="error-box"></div>
        </div>
    </div>

    <div class="form-group">
        <label for="contact_phone" class="col-sm-4 control-label">Телефон</label>

        <div class="col-lg-4">
            {!! Form::text("contact[$id][contact_phone]",$cont['contact_phone'], ['class' => 'form-control contact_phone', 'required','pattern'=>'\+380\d{3,12}', 'id' => 'phone', 'placeholder' => '+380930000000']) !!}
            <div class="error-box"></div>
        </div>
    </div>

    <div class="form-group">
        <label for="contact_email" class="col-sm-4 control-label">Email</label>

        <div class="col-lg-4">
            {!! Form::text("contact[$id][contact_email]",$cont['contact_email'], ['class' => 'form-control contact_email','required', 'id'=>'email', 'placeholder' => 'mymail@email.com']) !!}
             <div class="error-box"></div>
        </div>
    </div>



    <div class="form-group show-site guest" @if(old('type') === 'guest') @hide @endif>
        <label for="contact_url" class="col-sm-4 control-label">Мова</label>

        <div class="col-lg-4">
            {!! Form::select("contact[$id][contact_available_lang]", $languages, $cont['contact_available_lang'],
                [ 'class' => 'form-control contact_available_lang','required'
                ]) !!}
        </div>
    </div>
</div>

<?php $i++; }}else {  $i = 1; $id = 'z0';?>


<div class="contacts" ordinal="{{$i}}">
    <button type="button" class="close close-it-contact id" data-dismiss="modal" aria-label="Close" id="{{$id}}"><span aria-hidden="true">×</span></button>
    <div class="form-group">
        <label for="contact_email" class="col-sm-4 control-label">Основний Контакт</label>
        <div class="col-lg-4">
            <input type="radio" name="primary_contact" class="primary"  checked="enabled" value="z0" />
        </div>
    </div>
    <div class="form-group">
        <label for="contact_name" class="col-sm-4 control-label">Контактна особа</label>

        <div class="col-lg-4">
            {!! Form::text('contact['.$id.'][contact_name]',null, ['class' => 'form-control contact_name', 'id' => 'contactPerson', 'required', 'placeholder' => 'Прізвище, ім\'я та по батькові']) !!}
              <div class="error-box"></div>
        </div>
    </div>

    <div class="form-group">
        <label for="contact_name" class="col-sm-4 control-label">Контактна особа англійською</label>

        <div class="col-lg-4">
            {!! Form::text('contact['.$id.'][contact_name_en]',null, ['class' => 'form-control contact_name_en', 'id' => 'contactPersonEn', 'required', 'placeholder' => 'Name and Last Name']) !!}
              <div class="error-box"></div>
        </div>
    </div>

    <div class="form-group">
        <label for="contact_phone" class="col-sm-4 control-label">Телефон</label>

        <div class="col-lg-4">
            {!! Form::text('contact['.$id.'][contact_phone]',null, ['class' => 'form-control contact_phone', 'required','pattern'=>'\+380\d{3,12}', 'id' => 'phone', 'placeholder' => '+380930000000']) !!}
            <div class="error-box"></div>
        </div>
    </div>

    <div class="form-group">
        <label for="contact_email" class="col-sm-4 control-label">Email</label>

        <div class="col-lg-4">
            {!! Form::text('contact['.$id.'][contact_email]',null, ['class' => 'form-control contact_email','required', 'pattern'=>'[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$' ,'placeholder' => 'mymail@email.com', 'id'=>'email']) !!}
            <div class="error-box"></div>
        </div>
    </div>


    <div class="form-group show-site guest">
        <label for="contact_url" class="col-sm-4 control-label">Мова</label>

        <div class="col-lg-4">
            {!! Form::select('contact['.$id.'][contact_available_lang]', $languages, '',
                [ 'class' => 'form-control contact_available_lang','required'
                ]) !!}
        </div>
    </div>
    <br>
    </br>
</div>


<?php  }  ?>

<input type="hidden" name="deleted_ids" class="deleted_ids" value="0">
<div class="contact_container">
</div>
<input type="hidden" class="ordinal_number" value="<?php echo $i-1;?>">
<button data-namespace="tender" type="button" class="btn btn-info pull-lg add_contact_person">
    <span class="glyphicon glyphicon-plus "></span>{{Lang::get('keys.add_contact')}}
</button>

@if(!isset($organization))
    <div class="form-group terms" @if((old('type') === null || old('type') === 'customer' || old('type') === 'guest')) @hide @endif>
            <div class="col-md-1 col-md-offset-3" >
                @if((old('type') === 'customer' || old('type') === 'guest'))
                    {!! Form::checkbox('terms', 1, false, ['class' => 'agree-checkbox terms pull-right', 'required', 'disabled']) !!}
                @else
                    {!! Form::checkbox('terms', 1, false, ['class' => 'agree-checkbox terms pull-right', 'required', ]) !!}
                @endif
            </div>
        <label  class="col-md-5 control-label terms" style="padding: 0px; text-align: left;">Підтверджую ознайомлення з положеннями <a href="https://lp.zakupki.com.ua/reglament">Регламенту електронного майданчика "Zakupki UA" (у тому числі, Тарифів)</a> і <a href="{{route('user.offer')}}">Договору про надання послуг</a>, з якими погоджуюсь </label>
    </div>
@else
    <div class="form-group terms" @if($organization->type !== 'supplier') @hide @endif>
        <label  class="col-md-5 col-md-offset-4 control-label terms" style="padding: 0px; text-align: left;">Ознайомлений з положеннями <a href="https://lp.zakupki.com.ua/reglament">Регламенту електронного майданчика "Zakupki UA" (у тому числі, Тарифів)</a> і <a href="{{route('user.offer')}}">Договору про надання послуг</a></label>
    </div>
@endif
<script src="/js/validation_r.js"></script>
<script type="text/javascript">
    var schemes = <?php echo json_encode($schemes); ?>;

    $(document).ready(function () {

        if ($('#country').val() != 'UA') {
            $('.identity-label').text('Ідентифікатор');
            $('select[name="region_id"]').addClass('hidden');
            $('input[name="region_name"]').removeClass('hidden');
            $('.country_scheme').show();
            change_country($('#country').val());
            //$('.region-div').hide();
        } else {
            $('.country_scheme').hide();

        }
        console.log($('#country').val() );

        $('input[name=legal_name]').liTranslit({
			eventType:"blur",
			elAlias:$('input[name=legal_name_en]'),
			caseType:'inherit',
			reg:''
		});
    });

	function change_country($identifier) {
        var countryIso = $identifier;
        console.log('shit');
        var schemesSelectElement = $('#country_scheme');
        schemesSelectElement.empty(); //delete all options from select
        $.each(schemes[countryIso], function(key, value) {
            schemesSelectElement.append($("<option></option>")
                    .attr("value", value).text(value));
        });

        if (countryIso == 'UA') {
            //$('.region').show();
            $('select[name="region_id"]').removeClass('hidden');
            $('input[name="region_name"]').addClass('hidden');
            $('.country_scheme').hide();
            $('label[for="inputIdentifier"]').text('Код за ЄДРПОУ або реєстраційний номер облікової картки платника податків');
        } else {
            //$('.region').hide();
            $('select[name="region_id"]').addClass('hidden');
            $('input[name="region_name"]').removeClass('hidden');
            $('.country_scheme').show();
            $('.country_scheme').attr('disabled', false);
            $('label[for="inputIdentifier"]').text('Ідентифікатор');
        }
	}
    
    $(function () {
        $('#country').change(function () {
			change_country($(this).val());
			$('input[name="region_name"]').val('');
        });
         
        $('.type-select').change(function () {

            if ($(this).val() == 'supplier') {

                /** Прячет тип организации*/
                $(".show-kind").hide('fast');
                $('.kind-select').attr('disabled', true);

                /** Востанавлиевает инпуты которые не нужны были для Роли организации Гість*/
                $(".guest").find(':input:not(.kind-select)').each(function (i, v) {
                    $(v).prop('disabled', false);
                });
                $(".guest").show('fast');
                $('address-guest-wrapper').show('fast');

                if ($('#country').val() != 'UA') {
                    console.log('Inner' + $('#country').val());
                    $('.country_scheme').show();
                    $('#country_scheme').show();
                    $('.country_scheme').attr('disabled', false);
                }

                $('.terms').show('fast');
                $('.terms').attr('disabled', false);

            }

            if ($(this).val() == 'guest') {
                /** Дизеблид все не нужно для роли гость*/
//                $(".guest").hide('fast');
//                $(".guest").find(':input').each(function(i, v) {
//                    $(v).prop('disabled', true);
//                });
                $(".show-kind").hide('fast');
                $('.kind-select').attr('disabled', true);

                $('.terms').hide('fast');
                $('.terms').attr('disabled', true);
            }

            if ($(this).val() == 'customer') {
                /** востанавливает все инпуты*/
                $(".show-kind").show('fast');
                $(".guest").show('fast');

                $(".guest").find(':input').each(function (i, v) {
                    $(v).prop('disabled', false);
                });
                $('.kind-select').removeAttr('disabled');
                $('.address-guest-wrapper').show('fast');

                $('.terms').hide('fast');
                $('.terms').attr('disabled', true);

                $('.country_scheme').hide('fast');
                $('.country_scheme').attr('disabled', true);

                $('#country').val('UA');
                $('select[name="region_id"]').removeClass('hidden');
                $('input[name="region_name"]').addClass('hidden');
                $('select[name="region_id"]').val(26);
            }
        })
    });


</script>
<script type="text/javascript" src="/js/jquery.liTranslit.js"></script>