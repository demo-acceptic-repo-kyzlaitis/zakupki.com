<div class="modal fade" tabindex="-1" role="dialog" id="signature-set">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Закрити"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Підписати</h4>
            </div>
            <div class="modal-body">
                <form id="sign-form" class="form-horizontal">
                    <div class="form-group">
                        <input type="hidden" class="form-control" value="" name="SignID" id="SignID" />
                        <input type="hidden" class="form-control" value="" name="documentname" id="documentname">
                    </div>

                    <div class="form-group">
                        <label for="SignCertFile" class="control-label col-lg-2">Сертифiкат</label>
                        <div class="col-lg-10">
                            <input type="file" class="form-control" name="SignCertFile" id="SignCertFile" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="SignKeyFile" class="control-label col-lg-2">Приватний ключ</label>
                        <div class="col-lg-10">
                            <input type="file" class="form-control" name="SignKeyFile" id="SignKeyFile" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="SignKeyPassword" class="control-label col-lg-2">Пароль ключа</label>
                        <div class="col-lg-10">
                            <input type="password" class="form-control" name="SignKeyPassword" id="SignKeyPassword" />
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-lg-offset-2 col-lg-10">
                            <button class="btn btn-primary ladda-button" data-style="expand-right">{{Lang::get('keys.sign')}}</button>
                        </div>
                    </div>
                </form></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{Lang::get('keys.close')}}</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">

    $('#signature-set').on('show.bs.modal', function (e) {
        $(this).find('#SignID').val($(e.relatedTarget).data('id'));
        $(this).find('#documentname').val($(e.relatedTarget).data('documentname'));
    });

    $("#sign-form").submit(DoSign);
    function DoSign(e) {
        e.preventDefault();

        var idField = this["SignID"];
        var certField = this["SignCertFile"];
        var keyField = this["SignKeyFile"];
        var passwordField = this["SignKeyPassword"];
        var documentName = this["documentname"].value;

        if (!idField.value) {
            alert("No ID provided");
            return;
        }

        if (!certField.value) {
            alert("Оберіть сертифікат");
            return;
        }

        if (!keyField.value) {
            alert("Оберіть приватний ключ");
            return;
        }

        if (!passwordField.value) {
            alert("Заповніть поле пароль");
            return;
        }

        var documentId = idField.value;
        var certFile = certField.files[0];
        var keyFile = keyField.files[0];
        var password = passwordField.value;

        // show spinner
        var l = Ladda.create($("#sign-form button")[0]);
        l.start();

        SignDocument(documentName, documentId, certFile, keyFile, password, function () {
            l.stop();

            $('#signature-set').modal('hide');
            alert("Підпис накладенно успішно.");
            if (documentName=='contract') {
				$("#signature-confirm").click();
            } else {
            	location.reload();
            }

        }, function(e) {
            l.stop();

            if (e.message)
                e = e.message;
            else
                e = e.toString();

            alert("Помилка підписання: " + e);
        });
    }
</script>