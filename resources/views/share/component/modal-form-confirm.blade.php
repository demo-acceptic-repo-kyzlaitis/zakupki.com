<!-- Modal -->
<div class="modal fade" id="modal-confirm-{{$modalId}}">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{{$modalTitle}}</h4>
            </div>
            <div class="modal-body">
                {{$modalMessage}}
            </div>
            <div class="modal-footer">
                {!! Form::submit((isset($modalYesBtn)) ? $modalYesBtn : Lang::get('keys.yes'), ['class'=>'btn btn-info btn-confirm']) !!}
                <button type="button" class="btn btn-default" data-dismiss="modal">{{Lang::get('keys.cancel')}}</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(function(){
        $('.btn-confirm').click(function(){
            $('#modal-confirm-{{$modalId}}').modal('hide');
        })
    })
</script>