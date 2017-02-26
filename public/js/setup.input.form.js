/** 
 * Created by nik on 05.08.15.
 */
$(function () {

    $('[data-toggle="tooltip"]').tooltip();   

    $("body").on("click", '.close-it', function () {
        var count = $('.close-it').length;
        if (count == 1 && !$('.close-it').hasClass('accept-empty')) {
            alert("Должен быть хотя бы один лот");
        }
        else {
            $(this).closest("div.item-section").remove();
        }

    });
    $("body").on("click", '.close-it-contact', function () {
        var v = $(this).closest('form-group').find('.primary');
        console.log(v);
        var count = $('.close-it-contact').length;
        if (count == 1) {
            alert("Должен быть хотя бы один контакт");
        }
        else {
            var id = $(this).closest(".id").attr('id');
            var ids = $('.deleted_ids').val();
            $('.deleted_ids').val(ids+','+id);
            $(this).closest("div.contacts").remove();
        }

    });
    $("body").on("change", '.contact_person', function () {
        var arr = [];
        var i = 0;
        $('.cuurentId').each(function(){

            arr[i] = parseInt($(this).val());
            i++;
        });

        var token = $('meta[name="csrf-token"]').attr('content');
        var contact = $(this).closest('.contact');
        var id = contact.find(".contact_person option:selected").val();
        $.ajax({
            type: "POST",
            dataType:'html',
            headers: {
                'X-CSRF-TOKEN': token
            },
            url: "/organization/contacts",
            data: "id="+id,
            success: function(data){
                response = JSON.parse(data);
                var pars = parseInt(response.contact_id);
                if(jQuery.inArray(pars,arr) == -1) {
                    contact.find('.contact_name').val(response.contact_name);
                    contact.find('.cuurentId').val(response.contact_id);
                    contact.find('.contact_phone').val(response.contact_phone);
                    contact.find('.contact_email').val(response.contact_email);
                    contact.find('.contact_url').val(response.contact_url);
                    contact.find('.contact_available_lang').val(response.contact_available_lang);
                    contact.find('.contact_name_en').val(response.contact_name_en);
                }else{

                    alert('Такий контакт вже існує');
                }


                //$('[name=contact_name]').val(response.contact_name);
                //$('[name=contact_name_en]').val(response.contact_name_en);
                //$('[name=contact_phone]').val(response.contact_phone);
                //$('[name=contact_email]').val(response.contact_email);
                //$('[name=contact_url]').val(response.contact_url);
                //$('[name=contact_available_lang]').val(response.contact_available_lang);
            }
        })
    });

    $("body").on("click", '.readble', function () {
        var id = $(this).val();
        var token = $('meta[name="csrf-token"]').attr('content');
        $.ajax({
            type: "POST",
            dataType:'html',
            headers: {
                'X-CSRF-TOKEN': token
            },
            url: "/notification/readble",
            data: "id="+id,
            success: function(data){
                location.reload();
            }
        })
    });



    $("body").on("click", '.add_contact_person', function () {
        var ordinal_number = parseInt($('.ordinal_number').val());
        var index = ordinal_number+1;
        var id = 'z'+index;
        $('.ordinal_number').val(ordinal_number+1);
        var clone = $('div.contacts:first').clone();
        clone.attr('ordinal',index );
        clone.find('.primary').removeAttr('checked');
        clone.find('.id').attr('id',id);
        clone.find('.contact_name').attr('name', 'contact['+id+'][contact_name]');
        clone.find('.contact_name_en').attr('name', 'contact[' + id + '][contact_name_en]');
        clone.find('.contact_phone').attr('name', 'contact['+id+'][contact_phone]');
        clone.find('.contact_email').attr('name', 'contact['+id+'][contact_email]');
        clone.find('.contact_url').attr('name', 'contact['+id+'][contact_url]');
        clone.find('.contact_available_lang').attr('name', 'contact[' + id + '][contact_available_lang]');
        clone.find('.primary').val(id);
        clone.find('.contact_name').attr('value', '');
        clone.find('.contact_name_en').attr('value', '');
        clone.find('.contact_phone').attr('value', '');
        clone.find('.contact_email').attr('value', '');
        clone.find('.contact_url').attr('value', '');
        clone.find('.contact_available_lang').attr('value', '');
        $('.contact_container').append(clone);
    });
    $("body").on("click", '.primary', function () {
        var v = $(this).prop('checked');

       // $('.primary').removeAttr('checked')
        $(this).attr('checked');


    });
    $("body").on("click", '.search-show', function () {
        $('.forms').show(600);
        $('.console').hide();
    });
    $("body").on("click", '.csv-show', function () {
        $('.csv_uploader').show(600);
        $('.console').hide();
    });

    $("body").on("click", '.show-cosole', function () {
        $('.console').show(600);
        $('.forms').hide();
        $('.csv_uploader').hide();
    });

    $("body").on("focus", ".budjet", function() {
        var parent = $(this).closest('.budjet_amount');
        parent.find('.budjet-step').val('');
    });

    $("body").on("blur", ".budjet-step", function() {
        var parent = $(this).closest('.budjet_amount');
        var bud = parseFloat(parent.find('.budjet').val());
        var bud_min = bud * 0.5/100;
        var bud_max = bud * 3/100;
        var buStep = parseFloat(parent.find('.budjet-step').val());
        var one_procent = bud / 100;
        var res = buStep / one_procent;
        if (buStep >= bud_min && buStep <= bud_max){

            parent.find('.budjet-step-interest').val(roundPlus(res,2));
        }
    });


    $("body").on("blur", ".budjet-step-interest", function() {
        var parent = $(this).closest('.budjet_amount');
        var bud = parseFloat(parent.find('.budjet').val());
        var bud_min = bud * 0.5/100;
        var bud_max = bud * 3/100;
        var interest = parseFloat(parent.find('.budjet-step-interest').val());
        var result = bud * interest /100;

        if (result >= bud_min && result <= bud_max){
            parent.find('.budjet-step').val(roundPlus(result,2));
        }

    });
    $(window).load(function() {
        var parents = $('.budjet_amount');
        parents.each(function(){
            var bud = parseFloat($(this).find('.budjet').val());
            var bud_min = bud * 0.5/100;
            var bud_max = bud * 3/100;
            var buStep = parseFloat($(this).find('.budjet-step').val());
            $(this).find('.budjet-step').val(roundPlus(buStep,2))
            var one_procent = bud / 100;
            var res = buStep / one_procent;
            if (buStep >= bud_min && buStep <= bud_max){
                $(this).find('.budjet-step-interest').val(roundPlus(res,2));
            }
            else if (buStep < bud_min){
                //alert('Розмір мінімального кроку пониження ціни повинен бути від 0,5%');
                $(this).find('.budjet-step-interest').val(roundPlus(res,2));
            }
            else if (buStep > bud_max){
                //alert('Розмір мінімального кроку пониження ціни повинен до 3%');
                $(this).find('.budjet-step-interest').val(roundPlus(res,2));
            }
        });

        $.each($('.bid_qualify_status'), function () {
            $(this).change();
        })
        handleSelectWidget();
    });
    function roundPlus(x, n) { //x - число, n - количество знаков
        if(isNaN(x) || isNaN(n)) return '';
        var m = Math.pow(10,n);
        return Math.round(x*m)/m;
    }






    $("body").on("click", '.del-option', function () {
        console.log('sdf');
        $(this).closest("div.form-group").remove();
    });


    $("[data-number-format]").number(true, 2, '.', '');

    $("[date-time-picker]").datetimepicker({
        locale: 'ru',
        format: 'DD.MM.YYYY HH:mm'

    });

    $("[date-picker]").datetimepicker({
        locale: 'ru',
        format: 'DD.MM.YYYY'

    });
    function kitcut( text, limit) {
         text = text.trim();
         if( text.length <= limit) return text;
         text = text.slice( 0, limit); 
         lastSpace = text.lastIndexOf(" ");
         if( lastSpace > 0) { 
           text = text.substr(0, lastSpace);
         }
         return text + "...";
                    }

    /* добавить/удалить документ на форме тендера */
    var FilesBlock = {

        parent: $('.files'),
        generateElement: function (target) {
             $('.progress').asProgress({
                 'namespace': 'progress',
                'bootstrap': false,
                'min': 0,
                'max': 100,
                'goal': 100,
                'speed': 10
            });
            $('.hide_batton').attr('disabled','disabled');
            var counFiles = $('.countFiles').val();
            $('.countFiles').val(parseInt(counFiles)+1);
            var namespace = $(target).data('namespace');
            var index = parseInt($(target).data('index'), 10) + 1;
            var $fileBlock = $($('#text-template-' + namespace).html());
            var parent = $('.files.' + namespace);
            $('input#' + namespace + '-0', $fileBlock).data('index', index);
            $('input#' + namespace + '-0', $fileBlock).attr('id', namespace + '-' + index);
            $('label[for="' + namespace + '-0"]', $fileBlock).attr('for', namespace + '-' + index);
            parent.append($fileBlock);
        }
    };
    $('body').on('click', '.remove-file', function (event) {
        $(event.target).parent('.file').remove();
        var counFiles = $('.countFiles').val();
        var result = parseInt(counFiles)-1;
        $('.countFiles').val(result);
        if (result == 0){
            $('.hide_batton').removeAttr('disabled');
        }


    });

    $('body').on('change', 'input[type="file"]', function (event) {


    	//Выводит сообщение, что добавлен новый файл на замену предыдущего
        if ($(event.target).hasClass('upload')) {
        	showNewFileMessage($(event.target),event.target.files[0]);
        }
    	
    	var $fileBlock = $(event.target).parents('.file'),
            $fileDescription = $fileBlock.find('.file-description'),
            addFile = true;
        
        $fileBlock.find('label, select, .remove-file').toggleClass('hidden');
        // $fileBlock.find('select').select2();

        if ($fileBlock.find('input[name="confidential"]').length)
            handlerConfidential($fileBlock, $fileBlock.find('input[name="confidential"]')[0].checked);

        $.each(event.target.files, function () {

            if (this.size > 45 * 1048576) {
                $('#maxFilesAmount').modal();
                addFile = false;
            } else {
            
                var html = '<div class="fl-lef tooltips">' + kitcut(this.name, 20) + '<span class="tooltiptexts">'+ this.name + '</span>'+
                '</div>' + 
                '<div class="our_progress progress" role="progressbar" data-goal="-50" aria-valuemin="-100" aria-valuemax="0">'+
                '<div class="progress__bar"><span class="progress__label" ></span></div></div>'+
                '<span style="float: right">' + Math.round(this.size / 1024) + ' kB </span>';
                $fileDescription.html(html);
                setTimeout(function() { $('.progress').asProgress('go', 100) }, 500);

            }

        });

        if (!addFile) {
            $fileBlock.find('label, select, .remove-file').toggleClass('hidden');
        } else {
            FilesBlock.generateElement(event.target);
            $fileBlock.find('select').select2();
        }

    });
    
    //Показывает сообщение о новом файле на замену предыдущего
    function showNewFileMessage( $obj, $newfile) {
    	$element = $obj.parent().parent().parent().find('td:nth-child(2)');
    	$element.html('Додано новий файл <b>' + $newfile.name + '</b> для зміни.<br>Для збереження натисніть Оновити');
    	console.log($element);
    }

    $('body').on('change', 'input[name="confidential"]', function (event) {
        var $fileBlock = $(event.target).parents('.file');
        handlerConfidential($fileBlock, event.target.checked);
    });

    //$('body').on('change', 'select[name="cause"]', function (event) {
    //    var value = $(this).val();
    //    if (value == 'quick') {
    //        $('span[data-target="#procedure_cause"]').click();
    //        $(this).val($(this).children('option:first').val())
    //    }
    //});

    /*
     *
     * Установка параметров в зависимоти от того, конфиденциальный ли документ
     * */
    function handlerConfidential($fileBlock, checked) {
        if (checked) {
            $fileBlock.find('.confidentialCauseBlock').removeClass('hidden');
            $fileBlock.find('.confidentialCause').attr('required', 'required');
            $fileBlock.find('.confidential').val('1');
        } else {
            $fileBlock.find('.confidentialCauseBlock').addClass('hidden');
            $fileBlock.find('.confidentialCause').removeAttr('required');
            $fileBlock.find('.confidential').val('0');
        }
    }


    /* показывать/скрывать аддрес на форме тендера */
    $('body').on('change', 'input:radio.address-toggle-control', (function () {
        var base_block = $(this).closest('.addres_block');
        var position = base_block.find('input:radio.address-toggle-control:checked').val();

        if(position == 1){
            base_block.find(".original input").attr('disabled','disabled');
            base_block.find(".original select:not(.country)").attr('disabled','disabled');
            base_block.find('.original').hide();
            base_block.find(".sames input").removeAttr('disabled');
            base_block.find(".sames select:not(.country)").removeAttr('disabled');
            base_block.find('.sames').show();
        }else if(position == 0){
            base_block.find(".sames input").attr('disabled','disabled');
            base_block.find(".sames select:not(.country)").attr('disabled','disabled');
            base_block.find('.sames').hide();
            base_block.find(".original input").removeAttr('disabled');
            base_block.find(".original select:not(.country)").removeAttr('disabled');
            base_block.find('.original').show();
        }
    }));

    
    $('body').on('change', '.regions', (function () {
        var val = $(this).val();
        if (val == 0 ){
            var addresBlock = $(this).closest('.addres_block');

            addresBlock.find('.address').attr('readonly',true);
            addresBlock.find('.address').val('');
        }
        if (val != 0 ){
            var addresBlock = $(this).closest('.addres_block');
            addresBlock.find('.address').attr('readonly',false);
        }
    }));
    $( document ).ready(function() {
        var region_id = $('.regions');
        var id = region_id.val();
        if (id ==0){
            var addresBlock = region_id.closest('.addres_block');
            addresBlock.find('.address').attr('readonly',true);
            addresBlock.find('.address').val('');
        }

    });

    $(document.body).on('click', 'button.add-non-price', function () {
        var $_featuresContainer = $('.features-container.' + $(this).data('container')),
            featureIndex = parseInt($(this).data('feature'), 10);

        $(this).attr('disabled', true);
        $(this).find('span').removeClass('glyphicon-plus').addClass('glyphicon-refresh-animate glyphicon-refresh');

        var _button = this;
        var template = $(_button).data('template');
        var proc = $(_button).data('proc');

        $.get('/template/feature/' + $(this).data('namespace') + '/' + featureIndex + '?template=' + template + '&proc=' + proc).success(function (data) {
            $_featuresContainer.append(data);
            $(_button).attr('disabled', false);
            $(_button).data('feature', featureIndex + 1);
            $(_button).find('span').addClass('glyphicon-plus').removeClass('glyphicon-refresh-animate glyphicon-refresh');
        });
    });

    $(document.body).on('click', 'button.add-option', function () {
        var featureIndex = parseInt($(this).data('feature'), 10);

        var $_valuesContainer = $('.' + $(this).data('container')),
            valueIndex = parseInt($(this).data('value'), 10) + 1;


        $(this).attr('disabled', true);
        $(this).find('span').removeClass('glyphicon-plus').addClass('glyphicon-refresh-animate glyphicon-refresh');

        var _button = this;
        var proc = $(_button).data('proc');
        $.get('/template/feature-value/' + $(this).data('namespace') + '/' + featureIndex + '/' + valueIndex+ '?proc=' + proc).success(function (data) {
            $_valuesContainer.append(data);
            $(_button).attr('disabled', false);
            $(_button).data('value', valueIndex);
            $(_button).find('span').addClass('glyphicon-plus').removeClass('glyphicon-refresh-animate glyphicon-refresh');
        });
    });


    /* добавить лот на форме тендера */


    $(document.body).on('click', 'button.add-lot-section', function () {
        $('#errors').empty();
        var _form = $(this).parents('form');
        var tenderHidden = _form.find('input[name="tender_id"]');
        var url = (tenderHidden.val() > 0) ? '/draftTender/update' : '/draftTender/store';
        var dataForm = new FormData(_form[0]);

        var _button = this,
            $_lotsContainer = $('.lots-container'),
            proc = $(_button).data('proc'),
            organization = $(_button).data('organization'),
            lotIndex = parseInt($(this).data('lot'), 10) + 1;
        var template = $(this).data('template');

        $.ajax({
            url: url,
            type: "POST",
            dataType: "json",
            processData: false,
            contentType: false,
            data: dataForm,
            success: function (data) {
                if (data.status == 'created')
                    tenderHidden.val(data.tender_id);

                $(_button).attr('disabled', true);
                $(_button).find('span').removeClass('glyphicon-plus').addClass('glyphicon-refresh-animate glyphicon-refresh');

                $.get('/template/lot/' + lotIndex + '?template=' + template + '&proc=' + proc + '&organization=' + organization).success(function (data) {
                    $_lotsContainer.append(data);
                    $(_button).attr('disabled', false);
                    $(_button).data('lot', lotIndex);
                    $(_button).find('span').addClass('glyphicon-plus').removeClass('glyphicon-refresh-animate glyphicon-refresh');

                    handleSelectWidget();
                });
            },
            error: function (data) {
                $('.errors-create').removeAttr('hidden');
                for (var i = 0; i < data.responseJSON[0].length; i++) {
                    $("#errors").append('<li >' + data.responseJSON[0][i] + '</li>');
                }
                window.scrollTo(0, 0);
            }
        });
    });

    $(document.body).on('click', '.add-item-section', function () {
        var _button = this,
            lotIndex = $(_button).data('lot'),
            template = $(_button).data('template'),
            proc = $(_button).data('proc'),
            organization = $(_button).data('organization'),
            itemIndex = parseInt($(_button).data('item'), 10) + 1;
        var $_itemsContainer = $('.item-container-' + lotIndex);
        $(this).attr('disabled', true);
        $(_button).data('item', itemIndex);
        $(this).find('span').removeClass('glyphicon-plus').addClass('glyphicon-refresh-animate glyphicon-refresh');
        //var editable = $('#isEditable').data('editable');
        $.get('/template/item/' + lotIndex + '/' + itemIndex + '?template=' + template + '&proc=' + proc + '&organization=' + organization).success(function (data) {
            $_itemsContainer.append(data);
            $(_button).attr('disabled', false);
            $(_button).data('lot', lotIndex);
            $(_button).find('span').addClass('glyphicon-plus').removeClass('glyphicon-refresh-animate glyphicon-refresh');
            /*
             * Вешаем слушатели  на подгруженнй шаблон для динамически добавляемых класификаторов
             * */
            handleSelectWidget();
        });

    });

    $(document.body).on('click', '.add-item-section-plan', function () {
        var _button = this,
            itemIndex = parseInt($(_button).data('item'), 10) + 1;
        var $_itemsContainer = $('.items-container');
        $(this).attr('disabled', true);
        $(this).find('span').removeClass('glyphicon-plus').addClass('glyphicon-refresh-animate glyphicon-refresh');
       // var editable = $('#isEditable').data('editable');
        $.get('/template/planitem/' + itemIndex + '/' + $(_button).data('plan')).success(function (data) {
            $_itemsContainer.append(data);
            $(_button).attr('disabled', false);
            $(_button).data('item', itemIndex);
            $(_button).find('span').addClass('glyphicon-plus').removeClass('glyphicon-refresh-animate glyphicon-refresh');
            /*
             * Вешаем слушатели  на подгруженнй шаблон для динамически добавляемых класификаторов
             * */
            handleSelectWidget();
        });

    });

    $(document.body).on('focus', "[date-time-picker]", function () {
        $(this).datetimepicker({
            locale: 'ru',
            format: 'DD.MM.YYYY HH:mm'
        });
    });

    $(document.body).on('focus', "[date-picker]", function () {
        $(this).datetimepicker({
            locale: 'ru',
            format: 'DD.MM.YYYY'
        });
    });


    $(function () {
        $('#enquire-date-time, #tendering-date-time').datetimepicker({
            allowInputToggle: true,
            locale: 'ru',
            format: 'DD.MM.YYYY HH:mm'
        });
    });


    $(document.body).on('focus', ".classifier", function () {

        /*
         * Проверяем был ли уже установлен плагин на данный элемент
         * */
        if (!$(this).data('ui-autocomplete')) {
            $(this).autocomplete({
                source: "/tender/classifier/" + $('.classifier-selector').val(),
                minLength: 2,
                select: function (event, ui) {
                    $(this).next().val(ui.item.id);
                }
            });
        }
    });

    $(document.body).on('change', ".classifier-selector", function () {
        $('.changer').val('');
        var tmp = $(this).val();
        var _this = $(this);
        _this.parents('.form-group').find('.classifier').val('');
        _this.parents('.form-group').find('.classifier2').val('');
        $('.currentSelect').val(tmp);
        if (tmp == 1){
            $('.changer').addClass('cpv');
            $('.changer').removeClass('classifier');
        }else if(tmp == 0){
            $('.changer').removeClass('cpv');
            $('.changer').removeClass('classifier');
            $('.changer').removeClass('ui-autocomplete-input');
        }else{
            $('.changer').removeClass('cpv');
            $('.changer').addClass('classifier');

            if (tmp == 6) {
                $.ajax({
                    url: "/tender/classifier/" + tmp,
                    success: function (data) {
                        if (data.length == 1) {
                            _this.parents('.form-group').find('.classifier').val(data['0'].value);
                            _this.parents('.form-group').find('.classifier2').val(data['0'].id);
                        }
                    }
                });
            }
        }
    });

    $(document).ready(function(){
        var currentSelect = $('.currentSelect').val();
        if (currentSelect == 1){
            $('.changer').addClass('cpv');
            $('.changer').removeClass('classifier');
        }else if(currentSelect == 0){
            $('.changer').removeClass('cpv');
            $('.changer').removeClass('classifier');
            $('.changer').removeClass('ui-autocomplete-input');
        }else{
            $('.changer').removeClass('cpv');
            $('.changer').addClass('classifier');
        }
    });

    $(document.body).on('focus', ".cpv", function () {
        /*
         * Проверяем был ли уже установлен плагин на данный элемент
         * */
        var currentSelect = $('.currentSelect').val();
        if (!$(this).data('ui-autocomplete') && currentSelect != 0) {
            $(this).autocomplete({
                source: "/tender/classifier/1",
                minLength: 2,
                select: function (event, ui) {
                    $(this).next().val(ui.item.id);
                    var _additionalClassifier = $(this).parents('.form-group').next('.classifier_additional');
                    if (_additionalClassifier.length > 0) {
                        if (ui.item.value == '99999999-9 Не визначено') {
                            _additionalClassifier.removeClass('hidden');
                            _additionalClassifier.find('input').removeAttr('disabled').attr('required', true);
                        } else {
                            _additionalClassifier.addClass('hidden');
                            _additionalClassifier.find('input').attr('disabled', true).val('');
                        }
                    }
                }
            });
        }else if (currentSelect != 0){
            $(this).autocomplete({
                source: "/tender/classifier/1",
                minLength: 2,
                select: function (event, ui) {
                    $(this).next().val(ui.item.id);
                    var _additionalClassifier = $(this).parents('.form-group').next('.classifier_additional');
                    if (_additionalClassifier.length > 0) {
                        if (ui.item.value == '99999999-9 Не визначено') {
                            _additionalClassifier.removeClass('hidden');
                            _additionalClassifier.find('input').removeAttr('disabled').attr('required', true);
                        } else {
                            _additionalClassifier.addClass('hidden');
                            _additionalClassifier.find('input').attr('disabled', true).val('');
                        }
                    }
                }
            });
        }else{
            $(this).removeAttr('autocomplete');
            $('.changer').removeClass('cpv');
            $('.changer').removeClass('classifier');
        }
    });
    $(".cpv").autocomplete({
        source: "/tender/classifier/1",
        minLength: 2,
        select: function (event, ui) {
            $(this).next().val(ui.item.id);
            var _additionalClassifier = $(this).parents('.form-group').next('.classifier_additional');
            if (_additionalClassifier.length > 0) {
                if (ui.item.value == '99999999-9 Не визначено') {
                    _additionalClassifier.removeClass('hidden');
                } else {
                    _additionalClassifier.addClass('hidden');
                    _additionalClassifier.find('input').val('');
                }
            }
        }
    });


    $(document.body).on('focus', ".kekv", function () {
        /*
         * Проверяем был ли уже установлен плагин на данный элемент
         * */
        if (!$(this).data('ui-autocomplete'))
            $(this).autocomplete({
                source: "/tender/classifier/3",
                minLength: 2,
                select: function (event, ui) {
                    $(this).next().val(ui.item.id);
                }
            });
    });


    $('.dkpp-list-container').on('click', '.tree_dkpp', function () {
        alert('Codes.id '.$(this).data('id'));
    });

    $(".classifier").autocomplete({
        source: "/tender/classifier/2",
        minLength: 2,
        select: function (event, ui) {
            $(this).next().val(ui.item.id);
        }
    });

    $('body').on('change', '.classifier-selector', function () {
        $(".classifier").autocomplete('option', {source: '/tender/classifier/' + $(this).val()});
    });

    $("body").on("keyup", '.create-award-form #proposition_amount', function () {
        var awardAmount = $(this).val();
        var tenderAmount = $(this).attr('data-tender-amount');
        if ((awardAmount / tenderAmount >= 5) || (tenderAmount / awardAmount >= 5)) {
            $('.create-award-form [data-toggle="modal"]').attr('data-target', '#modal-confirm-award');
        } else {
            $('.create-award-form [data-toggle="modal"]').attr('data-target', '');
        }
    });
    $('.create-award-form #proposition_amount').keyup();

    $("body").on("click", "*[data-target='#modal-confirm-award']", function (e) {
        e.preventDefault();
    });




    $('.give-answer').click(function () {
        $(this).hide();
        $(this).parent().next().slideDown('fast');

        return false;
    });

    $('.answer-form').submit(function () {
        var id = $(this).attr('qid');
        $(this).find('textarea').attr("readonly", true);

        $.post($(this).attr('action'), $(this).serialize()).done(function (data) {
            $('#answer-' + id).hide();
            $('#answer-cont-' + id).html(data);
        });

        return false;
    });

    /*
     *
     * Настройка виджета дополнительных класификаторов
     * */
    function handleSelectWidget() {
        /*
         * Вешаем виджет автокомплита
         * */
        $('.classifier').each(function () {
            /*
             * Проверяем был ли уже установлен плагин на данный элемент
             * */
            if (!$(this).data('ui-autocomplete'))
                $(this).autocomplete({
                    source: "/tender/classifier/2", /* Поумолчанию запрос идет на дкпп*/
                    minLength: 2,
                    select: function (event, ui) {
                        $(this).next().val(ui.item.id);
                    }
                });
        });

        $('.classifier-selector').each(function () {

            if (!$(this).hasClass('change-listener-ready')) /* Предотвращаем попытку повесить обработчиков больше чем нужно */
                $(this).addClass('change-listener-ready')
                    .on('change', function () {
                        var selectedValue = $(this).val();

                        $(this).closest('.additional-codes')/* Плучаем общий котейнер для обоих элементов */
                            .find('.classifier2') /* Находим все инпуты */
                            .each(function (index, inputElement) {
                                if (inputElement.name != 'code_additional_id') {
                                    /* Меняем значение атрибута name (старое имя на новое )*/
                                    var positionOfLastOpeningBracket = inputElement.name.lastIndexOf('[');
                                    inputElement.name = inputElement.name.substring(0, positionOfLastOpeningBracket);
                                    positionOfLastOpeningBracket = inputElement.name.lastIndexOf('[');
                                    inputElement.name = inputElement.name.substring(0, positionOfLastOpeningBracket) + '[' + selectedValue + '][id]'
                                }
                            })/* Меняем ссылку автокомплита*/
                            .autocomplete('option', {source: '/tender/classifier/' + selectedValue})
                            .val('')/* Обнуляем запись */
                            .next()/* Также для скрытого элемента обнуляем данные*/
                            .val('');

                    });


        });
    }

    /*
     * Сохраняем чистую версию блока для дальнейшего клонирования
     * */

    //handleSelectWidget.cleanHtml = $('.additional-codes').get(0).outerHTML;
    /*
     * Первый запуск при загрузке страницы
     * */
    //handleSelectWidget();


    /*
     * Слушаем события на элементе добавления нового елемента классификаторов
     * */
    $(document.body).on('click', '.add-new-classifier', function (event) {
        /*
         * Останавливаем стандартное поведение события для ссылки (url не изменится)
         * */
        event.preventDefault();
        $(this).closest('.container-add-new-classifier').before(handleSelectWidget.cleanHtml);
        /*
         * Вешаем обработчики на селект
         * */
        handleSelectWidget();
    });
    /*
     * Слушаем события на элементе удаления елемента классификаторов
     * */
    $(document.body).on('click', '.delete-classifier', function () {

        if ($('.delete-classifier').length > 1)
            $(this).closest('.additional-codes').remove();/* плучаем общий котейнер и удаляем */

    });

    $("body").on("change", '.bid_qualify_status', function () {
        var status = $(this).val(),
            form = $(this).parents('form');
        form.find('.bid_qualify_field').hide();
        form.find('.bid_' + status).show();
    });

    $("body").on("change", '.bid_unsuccessful_titles', function () {
        var titles = $(this).val(), description = '',
            descriptionInput = $(this).parents('form').find('.unsuccessful_description');

        for (var i = 0; i < titles.length; i++) {
            description = description + groundsForRejection[titles[i]] + ', ';
        }
        description = description.substr(0, description.length - 2);
        descriptionInput.val(description);
    });
    $('body').on('click', '.add-contact', function () {
        var numberOfcontact = parseInt($('.count_added_contact').val());
        var contact =  $('div.contact_main').clone();
        contact.find("[name=contact_person]").attr('name','');
        contact.find('.cuurentId').val('');
        contact.find('.cuurentId').attr('name', 'additional_contact['+numberOfcontact+'][id]');

        contact.find('[name=contact_name]').val('');
       // contact.find('[name=contact_name]').attr('name', 'additional_contact['+numberOfcontact+'][contact_name]');
          contact.find('[name=contact_name]').removeAttr('name');

        contact.find('[name=contact_phone]').val('');
    //    contact.find('[name=contact_phone]').attr('name', 'additional_contact['+numberOfcontact+'][contact_phone]');
        contact.find('[name=contact_phone]').removeAttr('name');

        contact.find('[name=contact_email]').val('');
     //   contact.find('[name=contact_email]').attr('name', 'additional_contact['+numberOfcontact+'][contact_email]');
        contact.find('[name=contact_email]').removeAttr('name');

        contact.find('[name=contact_url]').val('');
      //  contact.find('[name=contact_url]').attr('name', 'additional_contact['+numberOfcontact+'][contact_url]');
        contact.find('[name=contact_url]').removeAttr('name');

        contact.find('[name=contact_available_lang]').val('');
     //   contact.find('[name=contact_available_lang]').attr('name', 'additional_contact['+numberOfcontact+'][contact_available_lang]');
        contact.find('[name=contact_available_lang]').removeAttr('name');

        contact.find('[name=contact_name_en]').val('');
       // contact.find('[name=contact_name_en]').attr('name', 'contact_name_en['+numberOfcontact+'][contact_name_en]');
        contact.find('[name=contact_name_en]').removeAttr('name');

        contact.find('.closer').css('display', 'block');
        contact.removeClass('contact_main');
        $('.count_added_contact').val(numberOfcontact+1);
        $(contact).before('<br><br>');
        $('.contacts').append(contact);
    });
    $("body").on("click", '.close-cont', function () {
            $(this).closest("div.contact").remove();
    });

    $('body').on("click", '.amount-edit', function () {
        $('.current_amount').hide();
        $('.amount').attr('type','number');
        $('.amount').focus();
    });
    $('body').on("click", '.confirment', function () {
        if (confirm("Шановний користувач, відміна Вашого рішення про відхилення пропозиції можлива лише через процедуру оскарження в уповноваженому органі. Ви впевнені в бажанні відхилити пропозицію?")) {
            return true;
        } else {
            return false;
        }


    });





    $('body').on("focusout", '.amount', function () {
        var max_value = $('.max').val();
        var val = $('.amount').val();
        if(parseInt(val) > parseInt(max_value)){
            alert('Нова сумма не повинна превищювати поточну сумму');
            $('.current_amount').html(max_value);
            $('.amount').val(max_value);
            $('.amount').focus();
        }else{
            $('.current_amount').show();
            $('.current_amount').html(val);
            $('.amount').attr('type','hidden');
            $('.place').html('<input size="10" type="hidden" name="amount" value="'+val+'">')
        }

  //      $('.amount').focus();

    });
    $('body').on('click', '.prolong-cvalification', function () {

       $(this).closest('.award').find('.add-file:last').trigger('click');
    });

    $(document.body).on('click', '#tender-cancel', function () {
        if(!confirm("Ви впевнені, що хочете відмінити закупівлю?")){
            return false;
        }
    });

    $(document.body).on('click', '#lot-cancel', function () {
        if(!confirm("Ви впевнені, що хочете відмінити лот?")){
            return false;
        }
    });


    $(document.body).on('click', '#bid-cancel', function () {
        if(!confirm("Ви впевнені, що хочете відмінити пропозицію?")){
            return false;
        }
    });

    $('#copyButton').click(function(){
        var copyDiv = document.getElementById('inputContainingTextToBeCopied');
        copyDiv.style.display = 'block';
        copyDiv.focus();
        document.execCommand('SelectAll');
        document.execCommand("Copy", false, null);
        copyDiv.style.display = 'none';
    });

    /**
     * agents scripts
     *
     * scripts usage
     *
     * /agent/create
     * /agent/id/edit
     */
    $('.add-codes2015').on('click', function() {
        var codeIndex = parseInt($('#code2015-set').attr('data-set-amount'));

        codeIndex = 1 + parseInt(codeIndex );

        $('#code2015-set').attr('data-set-amount', codeIndex);

        var clonedTpl = $('#code2015-item-template').clone().css('display', '').appendTo('.code2015-set');
        clonedTpl.find('.input-code2015').attr('name', 'codes2015['+codeIndex+']');
        clonedTpl.find('.input-code2015-hidden').attr('name', 'codes2015['+codeIndex+']');
    });

    $('.add-codes2010').on('click',  function() {
        var codeIndex = parseInt($('#code2010-set').attr('data-set-amount'));

        codeIndex = 1 + parseInt(codeIndex );

        $('#code2015-set').attr('data-set-amount', codeIndex);

        var clonedTpl = $('#code2010-item-template').clone().css('display', '').appendTo('.code2010-set');
        clonedTpl.find('.input-code2010').attr('name', 'codes2010['+codeIndex+']');
        clonedTpl.find('.input-code2010-hidden').attr('name', 'codes2010['+codeIndex+']');

    });


    $(document.body).on('click', '.remove-codes2010', function() {
        this.closest('.code2010-item').remove();
        console.log('shit10');
    });

    $(document.body).on('click', '.remove-codes2015', function() {
        this.closest('.code2015-item').remove();
        console.log('shit15');
    });

    //автокомлиты для кодов
    $(document.body).on('focus', '.agent-classifier', function () {

        if (!$(this).data('ui-autocomplete')) {
            $(this).autocomplete({
                source: "/tender/classifier/1",
                minLength: 2,
                select: function (event, ui) {
                    $(this).next().val(ui.item.id);
                }
            });
        }
    });

    $(document.body).on('focus', '.agent-classifier-2010', function () {

        if (!$(this).data('ui-autocomplete')) {
            $(this).autocomplete({
                source: "/tender/classifier/2",
                minLength: 2,
                select: function (event, ui) {
                    $(this).next().val(ui.item.id);
                }
            });
        }
    });

    $('body').on("change ", '.bid_amount', function () {
        var entity_amount = parseInt($('.entity_amount').val());
        var val = parseInt($('.bid_amount').val());
        var result = entity_amount * 0.8;
        var token = $('meta[name="csrf-token"]').attr('content');
        if (val < result){
            var conf = confirm('Загальна вартість Вашої пропозиції значно нижча ніж очікувана вартість закупівлі. Ви впевнені, що хочете продовжити?');
            if(conf == false){
                $('.bid_amount').val('');
                $('.bid_amount').focus();
            }else{
                $.ajax({
                    type: "POST",
                    dataType:'html',
                    headers: {
                        'X-CSRF-TOKEN': token
                    },
                    url: "/notification/bidConfirm",
                    data: "entity_amount="+entity_amount+"&bid_amount="+val,
                    success: function(data){
                       alert(data);
                    }
                })
            }
        }

    });


});


