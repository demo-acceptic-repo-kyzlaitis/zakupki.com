$(function() {
    $('.lots-container').on('change', '.guarantee_type', function() {
        var inputs = $(this).closest('.guarantee-section').find(':input');
        if($(this).val() == 'ns') {
            $.each(inputs, function(i) {
                if(i > 0) { //пропускаем первый елемент
                    $(this).attr('disabled', 'disabled');
                }
            });
        } else  {
            $(inputs).each(function() {
                $(this).removeAttr('disabled');
            });
        }
    });


    var percentLimit = 0.5;
    $('.lots-container').on('change', '.procurement_type', function() {
        if($(this).val() != 'pw') {
            percentLimit = 3;
            console.log(percentLimit);
        } else {
            percentLimit = 0.5;
            console.log(percentLimit);
        }
        console.log(percentLimit);
    });

    $('.lots-container').on('keyup', '.guarantee-amount', function () {
        var guaranteeVal = $(this).val();
        var budget = $(this).closest('.lot-container').find('.budjet').val();
        var guarantee_percent = $(this).closest('.lot-container').find('.guarantee-percent');

        if(!isNaN(budget) && budget) {
            var percent = roundPlus(guaranteeVal / budget * 100, 2)
            guarantee_percent.val(percent);
        }
    });


    $('.lots-container').on('keyup', '.guarantee-percent', function () {
        var percent = $(this).val();
        var budget = $(this).closest('.lot-container').find('.budjet').val();
        var guarantee_amount = $(this).closest('.lot-container').find('.guarantee-amount');

        if(!isNaN(budget) && budget) {
            var result = (percent / 100) * budget;
            guarantee_amount.val(result);
        }
    });

    function roundPlus(x, n) { //x - число, n - количество знаков
        if(isNaN(x) || isNaN(n)) return '';
        var m = Math.pow(10,n);
        return Math.round(x*m)/m;
    }
    $(window).load(function() {
        var guaranteesTypes = $('.guarantee_type');

        $.each(guaranteesTypes, function (i) {
            var type = $(this).val();
            console.log(type);
            var budjet = $(this).closest('.lot-container').find('.budjet').val();
            console.log('budjet:' + budjet);
            var guaranteeVal = $(this).closest('.lot-container').find('.guarantee-amount').val()
            if(type == 'dbg') {
                var inputs = $(this).closest('.guarantee-section').find(':input');
                $(inputs).each(function() {
                    console.log('clssname: ' + $(this).attr('class'));
                    if($(this).attr('class').indexOf('guarantee-percent') >= 0) {
                        var percent = roundPlus(guaranteeVal / budjet * 100, 2);
                        $(this).val(percent);
                    }
                    $(this).removeAttr('disabled');
                });
            }
        });
    });


});



