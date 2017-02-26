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