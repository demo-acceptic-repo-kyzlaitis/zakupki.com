
<div class="signature-validation">

    <?php $namespace = str_replace('\\', '', get_class($entity));?>
    <?php
    $documentType = strtolower(array_slice(explode('\\', get_class($entity)), -1)[0]);
    $hasSignature = false;
    if ($entity->documents) {
        foreach ($entity->documents as $document) {
            if ($document->title == 'sign.p7s' && $document->format == 'application/pkcs7-signature') {
                if($documentType != 'plan') {
                        if ($entity->signed == 1 ){
                            $hasSignature = true;
                            break;
                        }
                    } else {
                    $hasSignature = true;
                    break;
                }
            }
        }
    }
    ?>

            <!-- add IIT library -->
    <script type="text/javascript" src="/js/sign/euscpt.js"></script>
    <script type="text/javascript" src="/js/sign/euscpm.js"></script>
    <script type="text/javascript" src="/js/sign/euscp.js" async></script>

    <!-- add public script from openprocurement-crypto for prepareObject() -->
    <script type="text/javascript" src="https://cdn.rawgit.com/openprocurement-crypto/common/v.0.0.23/js/index.js"></script>

    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/ladda-bootstrap/0.9.4/spin.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/ladda-bootstrap/0.9.4/ladda.min.js"></script>

    <script type="text/javascript" src="/js/sign/promise.min.js"></script>
    <script type="text/javascript" src="/js/sign/jsondiffpatch.min.js"></script>
    <script type="text/javascript" src="/js/sign/signer.js"></script>
    @if ($hasSignature)
        <div class="alert alert-success" role="alert">
            <button class="btn btn-sm btn-primary" style="float:right" id="verify-signature"
                    data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> Перевірка…">
                {{Lang::get('keys.check')}}
            </button>

            <div class="signature-present" style="line-height:2em">Накладено електронний цифровий підпис</div>

            <div class="modal fade" tabindex="-1" role="dialog" id="verify-signature-result">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title"></h4>
                        </div>
                        <div class="modal-body"></div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">{{Lang::get('keys.close')}}</button>
                        </div>
                    </div>
                </div>
            </div>


            <script type="text/javascript">
                $("#verify-signature").click(function() {
                    var $this = $(this);
                    $this.button('loading');

                    VerifyDocument("{{$documentType}}", "{{$entity->id}}", function(info) {
                        $this.button('reset');
                        var $dlg = $("#verify-signature-result");
                        if (info === false) {
                            $dlg.find(".modal-title").text("Цифровий підпис невірний");
                            $dlg.find(".modal-body").text("Інформацію було змінено з моменту підпису");
                        } else if (!info) {
                            $dlg.find(".modal-title").text("Цифровий підпис відсутній");
                            $dlg.find(".modal-body").text("Неможливо перевірити те, чого немає");
                        } else {
                            var owner = info.GetOwnerInfo();
                            var timestamp = info.GetTimeInfo();

                            $dlg.find(".modal-title").text("Цифровий підпис вірний");
                            $dlg.find(".modal-body").html(
                                    "Підписувач: <b>" + owner.GetSubjCN() + "</b><br/>" +
                                    "ЦСК: <b>" + owner.GetIssuerCN() + "</b><br/>" +
                                    "Мітка часу: <b>" + timestamp.GetTime() + "</b>");
                        }
                        $dlg.modal('show');
                    }, function(e) {
                        $this.button('reset');
                        if (e.message)
                            e = e.message;
                        else if (e.responseText)
                            e = e.responseText;
                        else if (e.statusText)
                            e = e.statusText;
                        alert("Помилка перевірки підпису: " + e);
                    });
                });
            </script>
        </div>
    @else
            <div class="alert alert-danger signature-absent" role="alert">
                Електронний цифровий підпис відсутній
            </div>
    @endif
</div>
