'use strict';

$(document).ready(function () {

    $('input#name, input#email, input#Full_legal_name, input#Full_legal_name_eng,'+
        'input#phone, input#contactPerson, input#contactPersonEn, input#locality, input#postalIndex,'+
        'input#postalAddress, input#inputIdentifier').unbind().blur( function(){

        var id = $(this).attr('id'),
            val = $(this).val();

        var code = /\D/,
            len = val.length > 0,
            phone_r = /\+380\d{3,12}/,
            email_r = /^([a-zA-Z0-9_.-])+@([a-zA-Z0-9_.-])+\.([a-zA-Z])+([a-zA-Z])+/;

        var edrpoCondition = !code.test(val) && (val.length > 7 && val.length < 11 && val.length != 9),
            postalIndexCondition = val.length == 5 && !code.test(val),
            phoneConditional = val.length == 13 && phone_r.test(val),
            emailConditional = val != '' && email_r.test(val);

        var styles_err = {
            color : "red",
            display: "block"
        };

        var Onlylength = function(selector, condition, error_message){

            if(condition){
                $(selector).addClass('not_error').removeClass('error');
                $(selector).next('.error-box').hide(1000);
            }
            else{
                $(selector).removeClass('not_error')
                    .addClass('error');

                $(selector).next('.error-box')
                    .html(error_message)
                    .css(styles_err);
            }
        };

        switch(id)
        {
            // Проверка поля "Назва організації"
            case 'name': Onlylength('input#name', len, 'Поле "Назва організації" необхідно заповнити.');
                break;

            // Проверка поля "Повна юридична назва"
            case 'Full_legal_name': Onlylength('input#Full_legal_name', len, 'Поле "Повна юридична назва" необхідно заповнити.');
                break;

            // Проверка поля "Повна юридична назва англійською мовою"
            case 'Full_legal_name_eng': Onlylength('input#Full_legal_name_eng', len, 'Поле "Контактна особа англійською мовою" необхідно заповнити.');
                break;

            // Проверка поля "Код ЄДРПОУ"
            case 'inputIdentifier': Onlylength('input#inputIdentifier', edrpoCondition, 'Поле "Код ЄДРПОУ" повино складатись з 8 або 10 цифр.');
                break;

            // Проверка поля "Поштовий індекс"
            case 'postalIndex':

                if($('#country').val() == 'UA'){
                    Onlylength('input#postalIndex', postalIndexCondition, 'Поле "Поштовий індекс" має містити 5 цифр.');
                }
                else{
                    Onlylength('input#postalIndex', len, 'Поле "Поштовий індекс" необхідно заповнити.');
                }
                break;

            // Проверка поля "Населений пункт"
            case 'locality': Onlylength('input#locality', len, 'Поле "Населений пункт" необхідно заповнити.');
                break;

            // Проверка поля "Поштова адреса"
            case 'postalAddress': Onlylength('input#postalAddress', len, 'Поле "Поштова адреса" необхідно заповнити.');
                break;

            // Проверка поля "Контактна особа"
            case 'contactPerson': Onlylength('input#contactPerson', len, 'Поле "Контактна особа" необхідно заповнити.');
                break;

            // Проверка поля "Контактна особа англійською"
            case 'contactPersonEn': Onlylength('input#contactPersonEn', len, 'Поле "Контактна особа англійською" необхідно заповнити.');
                break;

            // Проверка поля "Телефон"
            case 'phone':

                if($('#country').val() == 'UA'){
                    Onlylength('input#phone', phoneConditional, 'Поле "Телефон" повинно бути у вказанному форматі.');
                }
                else{
                    Onlylength('input#phone', len, 'Поле "Телефон" необхідно заповнити.');
                }
                break;

            // Проверка поля "email"
            case 'email': Onlylength('input#email', emailConditional, 'Поле повинно містити правильну email-адресу.');
                break;

        } // end switch(...)

    }); // end blur()

    $('form').submit(function(e){

        if(!$('.error').length > 0) return true;

        else{
            $('.error-box').text('Заполните поле');
            e.preventDefault();
            return false;
        }
    }); // end submit()
});