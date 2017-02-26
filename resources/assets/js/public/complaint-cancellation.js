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