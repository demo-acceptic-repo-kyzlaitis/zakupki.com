function filter(filterName, filterUrl){
    var token = $('meta[name="csrf-token"]').attr('content');
    $.ajax({
        url: filterUrl,
        type: "POST",
        headers: {
            'X-CSRF-TOKEN': token
        },
        data: $('#filter-form').serialize(),
        success: function (data) {
            console.log('success');
            if($('#'+filterName) && data){
                $('#'+filterName).html(data);
            }
        },
        error: function () {
            console.log('error');
        }
    });
    return false;
}

function cancelFilter(filterName, filterUrl){
    $('#filter-form input[type="text"]').each(function(){
        $(this).val('');
    });
    var token = $('meta[name="csrf-token"]').attr('content');
    $.ajax({
        url: filterUrl,
        type: "POST",
        headers: {
            'X-CSRF-TOKEN': token
        },
        data: $('#filter-form').serialize(),
        success: function (data) {
            console.log('success');
            if($('#'+filterName) && data){
                $('#'+filterName).html(data);
            }
        },
        error: function () {
            console.log('error');
        }
    });
    return false;
}


