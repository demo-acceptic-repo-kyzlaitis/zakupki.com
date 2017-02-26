$('.doc-download').on('click', function(event) {
    var docName = $(event.target).text()
    $.notify({
        title: "Документ не синхронізован с ЦБД.<br>",
        message: "Для завантаження документа (" + docName +") до ЦБД потрібно зберегти тендер (закупівлю)"
    },{
        //settings
        type: 'danger',
        animate: {
            enter: 'animated bounceIn',
            exit: 'animated bounceOut'
        }
    });
});
