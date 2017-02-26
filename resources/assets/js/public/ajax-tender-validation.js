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


