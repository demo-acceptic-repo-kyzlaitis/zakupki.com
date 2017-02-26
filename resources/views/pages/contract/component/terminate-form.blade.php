<?php
if($terminateType == 'fail') {
    $disabled    = $contract->termination_details == '' ? 'disabled' : '';
    $showMessage = $contract->termination_details == '' ? true : false;
    $messageId   = 'infoMessage';
}



if($terminateType == 'success') {
    $disabled    = $contract->amount_paid == 0 ? 'disabled' : '';
    $showMessage = $contract->amount_paid == 0 ? true : false;
    $messageId   = 'infoMessage';
}

?>

{!! Form::model($contract,['route' => ['contract.update', $contract->id],
'method'=>'PUT',
'enctype'=>'multipart/form-data',
'class'=>'form-horizontal',]) !!}

<div class="row">
    <div class="col-md-12">
        @include('share.component.signature', ['entity' => $contract])
    </div>
</div>

<fieldset>


    <div class="form-group">
        <label for="reason" class="col-sm-4 control-label">Фактично оплачена сума</label>

        <div class="col-md-4">
            <div class='input-group date'>
                {!! Form::text("amount_paid",null,['class' => 'form-control']) !!}
            </div>
        </div>

    </div>

    @if ($terminateType != 'success')
    <div class="form-group">
        <label for="reason" class="col-sm-4 control-label">Причина розірвання</label>

        <div class="col-lg-4">

            {!! Form::textarea('termination_details', null, ['class' => 'form-control', 'placeholder' => '', 'required'])  !!}
        </div>
    </div>
    @endif



    @if ($contract->documents()->count())
        <div class="form-group">
            <div class="col-md-8 col-md-offset-4">
                @include('share.component.document-list', ['entity' => $contract, 'size' => 'file-icon-sm'])
            </div></div>



    @endif

    @include('share.component.add-file-component',['documentTypes' => [], 'index' => 0, 'namespace' => 'contract', 'inputName' => 'contract', '_buttonName' => 'Додати скан договору'])


    <hr>
    <div class="form-group">
        <div class="col-lg-12 text-center">
            <a href="#" data-toggle="modal" data-target="#modal-confirm-terminate" class="btn btn-info">{{Lang::get('keys.save')}}</a>
            @include('share.component.modal-form-confirm', ['modalId' => 'terminate',
            'modalTitle' => 'Звітувати про виконання договору', 'modalMessage' => 'Ця операція призведе до незворотних дій. Ви підтверджуєте?'])

            <a @if(!$showMessage) href="#" data-toggle="modal" data-target="#signature-set" @else  href="#" data-toggle="modal" data-target="#{{$messageId}}" data-id="{{$contract->id}}" data-documentname="contract"@endif
               class="btn btn-success" {{$disabled}}>{{Lang::get('keys.sign_ecp_close')}}</a>

            @if($showMessage)
                @include('share.component.modal-info', ['modalNamespace' => $messageId ,'modalTitle' => '', 'modalMessage' => 'Для того, щоб розірвати договір, додайте документ та натисніть Зберегти.'])
            @endif

        </div>
    </div>


</fieldset>
{!! Form::close() !!}
<<<<<<< HEAD


<div class="modal fade" tabindex="-1" role="dialog" id="signature-set">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="{{Lang::get('keys.close')}}"><span
                            aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Підписати</h4>
            </div>
            <div class="modal-body">
                <form id="sign-form" class="form-horizontal">
                    <div class="form-group">
                        <input type="hidden" class="form-control" value="{{$contract->id}}" name="SignID"
                               id="SignID"/>
                    </div>

                    <div class="form-group">
                        <label for="SignCertFile" class="control-label col-lg-2">Сертифiкат</label>
                        <div class="col-lg-10">
                            <input type="file" class="form-control" name="SignCertFile" id="SignCertFile"/>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="SignKeyFile" class="control-label col-lg-2">Приватний ключ</label>
                        <div class="col-lg-10">
                            <input type="file" class="form-control" name="SignKeyFile" id="SignKeyFile"/>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="SignKeyPassword" class="control-label col-lg-2">Пароль ключа</label>
                        <div class="col-lg-10">
                            <input type="password" class="form-control" name="SignKeyPassword"
                                   id="SignKeyPassword"/>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-lg-offset-2 col-lg-10">
                            <button class="btn btn-primary ladda-button" data-style="expand-right">{{Lang::get('keys.sign')}}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{Lang::get('keys.close')}}</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">

    $("#sign-form").submit(DoSign);
    function DoSign(e) {
        e.preventDefault();

        var idField = this["SignID"];
        var certField = this["SignCertFile"];
        var keyField = this["SignKeyFile"];
        var passwordField = this["SignKeyPassword"];

        if (!idField.value) {
            alert("No ID provided");
            return;
        }

        if (!certField.value) {
            alert("No certificate selected");
            return;
        }

        if (!keyField.value) {
            alert("No private key selected");
            return;
        }

        if (!passwordField.value) {
            alert("Enter password");
            return;
        }

        var documentId = idField.value;
        var certFile = certField.files[0];
        var keyFile = keyField.files[0];
        var password = passwordField.value;

        // show spinner
        var l = Ladda.create($("#sign-form button")[0]);
        l.start();

        SignDocument("contract", documentId, certFile, keyFile, password, function () {
            l.stop();
            _usleep(100);
            $('#signature-set').modal('hide');

            alert("Підпис накладенно успішно.");
            location.href = '{{route('contract.terminated', [$contract->id])}}';

        }, function (e) {
            l.stop();

            if (e.message)
                e = e.message;
            else
                e = e.toString();

            alert("Signing error: " + e);
        });
    }
</script>

=======
@include('share.component.modal-ecp')
>>>>>>> f7aef59657d747c5309d9d50155ea83e07a6828a


