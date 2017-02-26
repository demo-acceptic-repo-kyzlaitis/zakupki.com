$('.double-click').dblclick(function() {
    if($(this).is('[readonly]')) {
        $(this).removeAttr('readonly');
    } else {
        $(this).prop('readonly', true);
    }
});


$('#save-email-template').on('click', function() {
    var filePath = $(this).data('path');
    var csrf = $('input[name="_token"]').val();
    var fileContent = $('#file-template-content').val();
    console.log(fileContent);
    console.log(csrf);
    console.log(filePath);
    $.ajax({
        url: "/admin/email/updateTemplate",
        type:"POST",
        data: {filePath: filePath, _token: csrf, content: fileContent},
        success: function(data){
            $.notify({
                title: '<strong>Успех!</strong>',
                message: 'Файл перезаписан.'
            },{
                type: 'success'
            });
            $(this).prop('readonly', true);
        },
        error: function (request, status, error) {
            $.notify({
                title: '<strong>Fail!</strong>',
                message: 'Что-то пошло не так!'
            },{
                type: 'error'
            });
            console.log('error');
        }
    });
});

$('#restore-copy').on('click', function() {
    var filePath = $(this).data('path');
    var csrf = $('input[name="_token"]').val();
    var fileContent = $('#file-template-content').val();
    $.ajax({
        url: "/admin/email/restoreCopy",
        type:"POST",
        data: {filePath: filePath, _token: csrf, content: fileContent},
        success: function(data){
            $.notify({
                title: '<strong>Успех!</strong>',
                message: 'Файл востановлен '
            },{
                type: 'success'
            });
            $(this).prop('readonly', true);
        },
        error: function (request, status, error) {
            $.notify({
                title: '<strong>Fail!</strong>',
                message: 'Файл не востановился. Скорее всего копия файла не была создана до этого.'
            },{
                type: 'danger'
            });
            console.log('error');
        }
    });
});
$(document).ready(function() {
    $(".tenderStatuses").select2({theme: "bootstrap"});
    $(".kinds").select2({theme: "bootstrap"});
    $(".regions").select2({theme: "bootstrap"});
    $(".procedureTypes").select2({theme: "bootstrap"});
});