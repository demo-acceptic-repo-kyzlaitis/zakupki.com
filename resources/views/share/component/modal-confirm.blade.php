<!-- Modal -->
<div class="modal fade" id="delete{{$modalNamespace}}">
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
                <a href="#" type="button" class="btn-ok btn btn-danger">{{Lang::get('keys.yes')}}</a>
                <button type="button" class="btn btn-default" data-dismiss="modal">{{Lang::get('keys.cancel')}}</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(function(){
        $('#delete{{$modalNamespace}}').on('show.bs.modal', function(e) {
            $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
        });
    })
</script>