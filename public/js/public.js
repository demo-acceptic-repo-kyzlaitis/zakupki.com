var complaintId = null;
$('#cancel-complaint-form').submit(function() {
    $.ajax({
        url: "/complaint/cancel",
        type:"POST",
        data: $('#cancel-complaint-form').serialize(),
        success: function(data){
            $('#complaint-modal').modal('toggle');
            $.notify({
                message: 'Вимога буде відкликана.'
            }, {
                type: 'success'
            });
            $('[data-complaint-id="' + complaintId+'"] button:first-child').prop('disabled', true);
            $('[data-complaint-id="' + complaintId+'"] span.complaint-status').text('Відкликано');

        },
        error: function (request, status, error) {
            $.notify({
                message: 'При відхиленні зверння винакла помилка.'
            }, {
                type: 'danger'
            });
        }
    });
    return false;
});


$('.complaint').on('click', function(e){
    complaintId = $(this).data('complaint-id');
    $('#hidden-complaint-id').remove();
    $('<input>').attr({
        type: 'hidden',
        id: 'hidden-complaint-id',
        value: complaintId,
        name: 'complaintId'
    }).appendTo('#cancel-complaint-form');
});
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




$("#submit-tender").on('click', function(event) {
    $('#errors').empty();
    $.ajax({
        url: "/tender",
        type: "POST",
        dataType: "json",
        data: $('#create-tender-form').serialize(),
        success: function (data) {
            $('#create-tender-form').submit();
        },
        error: function (data) {
            $('.errors-create').removeAttr('hidden');
            for (var i = 0; i < data.responseJSON[0].length; i++) {
                $("#errors").append('<li >' + data.responseJSON[0][i] + '</li>');
            }
            window.scrollTo(0, 0);

        }
    });
    return false;
});

$("#submit-edit-btn").on('click', function(event) {
    $('#errors').empty();
    var url = $('#edit-form-submit').attr('action');
    console.log(url);

    $.ajax({
        url: url,
        type: "POST",
        dataType: "json",
        data: $('#edit-form-submit').serialize(),
        success: function (data) {
            $('#edit-form-submit').submit();
        },
        error: function (data) {
            $('.errors-create').removeAttr('hidden');
            for (var i = 0; i < data.responseJSON[0].length; i++) {
                $("#errors").append('<li >' + data.responseJSON[0][i] + '</li>');
            }
            window.scrollTo(0, 0);

        }
    });
    return false;
});



$('.bid-amount, .budjet').on('keydown', function(event) {
    var key = event.which ? event.which : event.keyCode;
    console.log(key);
    if (key == 110 || key == 188 || key == 191) {
        event.preventDefault();
        var value = $(this).val();
        console.log(value);
        $(this).val(value + ".");
    }


});

$('.lots-container').on('keydown', '.guarantee-percent', function(event) {
    var key = event.which ? event.which : event.keyCode;
    console.log(key);
    if (key == 110 || key == 188 || key == 191) {
        event.preventDefault();
        var value = $(this).val();
        console.log(value);
        $(this).val(value + ".");
    }

});

$('.lots-container').on('keydown', '.guarantee-amount', function(event) {
    var key = event.which ? event.which : event.keyCode;
    console.log(key);
    if (key == 110 || key == 188 || key == 191) {
        event.preventDefault();
        var value = $(this).val();
        console.log(value);
        $(this).val(value + ".");
    }

});

$('.lots-container').on('keydown', '.budjet-step-interest', function(event) {
    var key = event.which ? event.which : event.keyCode;
    console.log(key);
    if (key == 110 || key == 188 || key == 191) {
        event.preventDefault();
        var value = $(this).val();
        console.log(value);
        $(this).val(value + ".");
    }

});


$('.lots-container').on('keydown', '.budjet-step', function(event) {
    var key = event.which ? event.which : event.keyCode;
    console.log(key);
    if (key == 110 || key == 188 || key == 191) {
        event.preventDefault();
        var value = $(this).val();
        console.log(value);
        $(this).val(value + ".");
    }
});

//todo refactor code duplication
/*
 * bootstrap-session-timeout
 * www.orangehilldev.com
 *
 * Copyright (c) 2014 Vedran Opacic
 * Licensed under the MIT license.
 */

(function($) {
    /*jshint multistr: true */
    'use strict';
    $.sessionTimeout = function(options) {
        var defaults = {
            title: 'Your Session is About to Expire!',
            message: 'Your session is about to expire.',
            logoutButton: 'Logout',
            keepAliveButton: 'Stay Connected',
            keepAliveUrl: '/keep-alive',
            ajaxType: 'POST',
            ajaxData: '',
            redirUrl: '/timed-out',
            logoutUrl: '/log-out',
            warnAfter: 10000, // 15 minutes
            redirAfter: 1200000, // 20 minutes
            keepAliveInterval: 5000,
            keepAlive: true,
            ignoreUserActivity: false,
            onStart: false,
            onWarn: false,
            onRedir: false,
            countdownMessage: false,
            countdownBar: false,
            countdownSmart: false
        };

        var opt = defaults,
            timer,
            countdown = {};

        // Extend user-set options over defaults
        if (options) {
            opt = $.extend(defaults, options);
        }

        // Some error handling if options are miss-configured
        if (opt.warnAfter >= opt.redirAfter) {
            console.error('Bootstrap-session-timeout plugin is miss-configured. Option "redirAfter" must be equal or greater than "warnAfter".');
            return false;
        }

        // Unless user set his own callback function, prepare bootstrap modal elements and events
        if (typeof opt.onWarn !== 'function') {
            // If opt.countdownMessage is defined add a coundown timer message to the modal dialog
            var countdownMessage = opt.countdownMessage ?
                '<p>' + opt.countdownMessage.replace(/{timer}/g, '<span class="countdown-holder"></span>') + '</p>' : '';
            var coundownBarHtml = opt.countdownBar ?
                '<div class="progress"> \
                  <div class="progress-bar progress-bar-striped countdown-bar active" role="progressbar" style="min-width: 15px; width: 100%;"> \
                    <span class="countdown-holder"></span> \
                  </div> \
                </div>' : '';

            // Create timeout warning dialog
            $('body').append('<div class="modal fade" id="session-timeout-dialog"> \
              <div class="modal-dialog"> \
                <div class="modal-content"> \
                  <div class="modal-header"> \
                    <h4 class="modal-title">' + opt.title + '</h4> \
                  </div> \
                  <div class="modal-body"> \
                    <p>' + opt.message + '</p> \
                    ' + countdownMessage + ' \
                    ' + coundownBarHtml + ' \
                  </div> \
                  <div class="modal-footer"> \
                    <button id="session-timeout-dialog-logout" type="button" class="btn btn-default">' + opt.logoutButton + '</button> \
                    <button id="session-timeout-dialog-keepalive" type="button" class="btn btn-primary" data-dismiss="modal">' + opt.keepAliveButton + '</button> \
                  </div> \
                </div> \
              </div> \
             </div>');

            // "Logout" button click
            $('#session-timeout-dialog-logout').on('click', function() {
                window.location = opt.logoutUrl;
            });
            // "Stay Connected" button click
            $('#session-timeout-dialog').on('hide.bs.modal', function() {
                // Restart session timer
                startSessionTimer();
            });
        }

        // Reset timer on any of these events
        if (!opt.ignoreUserActivity) {
            var mousePosition = [-1, -1];
            $(document).on('keyup mouseup mousemove touchend touchmove', function(e) {
                if (e.type === 'mousemove') {
                    // Solves mousemove even when mouse not moving issue on Chrome:
                    // https://code.google.com/p/chromium/issues/detail?id=241476
                    if (e.clientX === mousePosition[0] && e.clientY === mousePosition[1]) {
                        return;
                    }
                    mousePosition[0] = e.clientX;
                    mousePosition[1] = e.clientY;
                }
                startSessionTimer();

                // If they moved the mouse not only reset the counter
                // but remove the modal too!
                if ($('#session-timeout-dialog').length > 0 &&
                    $('#session-timeout-dialog').data('bs.modal') &&
                    $('#session-timeout-dialog').data('bs.modal').isShown) {
                    // http://stackoverflow.com/questions/11519660/twitter-bootstrap-modal-backdrop-doesnt-disappear
                    $('#session-timeout-dialog').modal('hide');
                    $('body').removeClass('modal-open');
                    $('div.modal-backdrop').remove();

                }
            });
        }

        // Keeps the server side connection live, by pingin url set in keepAliveUrl option.
        // KeepAlivePinged is a helper var to ensure the functionality of the keepAliveInterval option
        var keepAlivePinged = false;

        function keepAlive() {
            if (!keepAlivePinged) {
                // Ping keepalive URL using (if provided) data and type from options
                $.ajax({
                    type: opt.ajaxType,
                    url: opt.keepAliveUrl,
                    data: opt.ajaxData
                });
                keepAlivePinged = true;
                setTimeout(function() {
                    keepAlivePinged = false;
                }, opt.keepAliveInterval);
            }
        }

        function startSessionTimer() {
            // Clear session timer
            clearTimeout(timer);
            if (opt.countdownMessage || opt.countdownBar) {
                startCountdownTimer('session', true);
            }

            if (typeof opt.onStart === 'function') {
                opt.onStart(opt);
            }

            // If keepAlive option is set to "true", ping the "keepAliveUrl" url
            if (opt.keepAlive) {
                keepAlive();
            }

            // Set session timer
            timer = setTimeout(function() {
                // Check for onWarn callback function and if there is none, launch dialog
                if (typeof opt.onWarn !== 'function') {
                    $('#session-timeout-dialog').modal('show');
                } else {
                    opt.onWarn(opt);
                }
                // Start dialog timer
                startDialogTimer();
            }, opt.warnAfter);
        }

        function startDialogTimer() {
            // Clear session timer
            clearTimeout(timer);
            if (!$('#session-timeout-dialog').hasClass('in') && (opt.countdownMessage || opt.countdownBar)) {
                // If warning dialog is not already open and either opt.countdownMessage
                // or opt.countdownBar are set start countdown
                startCountdownTimer('dialog', true);
            }
            // Set dialog timer
            timer = setTimeout(function() {
                // Check for onRedir callback function and if there is none, launch redirect
                if (typeof opt.onRedir !== 'function') {
                    window.location = opt.redirUrl;
                } else {
                    opt.onRedir(opt);
                }
            }, (opt.redirAfter - opt.warnAfter));
        }

        function startCountdownTimer(type, reset) {
            // Clear countdown timer
            clearTimeout(countdown.timer);

            if (type === 'dialog' && reset) {
                // If triggered by startDialogTimer start warning countdown
                countdown.timeLeft = Math.floor((opt.redirAfter - opt.warnAfter) / 1000);
            } else if (type === 'session' && reset) {
                // If triggered by startSessionTimer start full countdown
                // (this is needed if user doesn't close the warning dialog)
                countdown.timeLeft = Math.floor(opt.redirAfter / 1000);
            }
            // If opt.countdownBar is true, calculate remaining time percentage
            if (opt.countdownBar && type === 'dialog') {
                countdown.percentLeft = Math.floor(countdown.timeLeft / ((opt.redirAfter - opt.warnAfter) / 1000) * 100);
            } else if (opt.countdownBar && type === 'session') {
                countdown.percentLeft = Math.floor(countdown.timeLeft / (opt.redirAfter / 1000) * 100);
            }
            // Set countdown message time value
            var countdownEl = $('.countdown-holder');
            var secondsLeft = countdown.timeLeft >= 0 ? countdown.timeLeft : 0;
            if (opt.countdownSmart) {
                var minLeft = Math.floor(secondsLeft / 60);
                var secRemain = secondsLeft % 60;
                var countTxt = minLeft > 0 ? minLeft + ' хвилин' : '';
                if (countTxt.length > 0) {
                    countTxt += ' ';
                }
                countTxt += secRemain + ' секунд';
                countdownEl.text(countTxt);
            } else {
                countdownEl.text(secondsLeft + " секунд");
            }

            // Set countdown message time value
            if (opt.countdownBar) {
                $('.countdown-bar').css('width', countdown.percentLeft + '%');
            }

            // Countdown by one second
            countdown.timeLeft = countdown.timeLeft - 1;
            countdown.timer = setTimeout(function() {
                // Call self after one second
                startCountdownTimer(type);
            }, 1000);
        }

        // Start session timer
        startSessionTimer();

    };
})(jQuery);

$( document ).ready(function() {
    $.sessionTimeout({
        title: 'Завершення робочої сесії',
        message: 'Натисніть Завершити, щоб завершити сесію і вийти з особистого кабінету або Продовжити, якщо хочете продовжити роботу в особистому кабінеті',
        countdownBar: true,
        countdownMessage: 'Ваша робоча сесія завершиться через {timer}',
        logoutButton: 'Завершити',
        keepAliveButton: 'Продовжити',
        ignoreUserActivity: true, //не будет срабатывать при движении мышки
        warnAfter: 1200000, //1200000, // показать окно завершения сесси через 20
        redirAfter: 1800000, //600000, // 10 минут
        countdownSmart: true,
        logoutUrl: '/logout',
        redirUrl: '/logout',
        keepAliveUrl: '/keep-alive',
        ajaxData: { _token: $("head > meta[name='csrf-token']").attr('content') },
        keepAlive: true
    });
});
//# sourceMappingURL=public.js.map
