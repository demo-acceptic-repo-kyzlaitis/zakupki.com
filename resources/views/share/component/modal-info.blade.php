<!-- Modal -->
<div class="modal fade" role="dialog" id="{{$modalNamespace}}">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">{{$modalTitle}}</h4>
            </div>
            <div class="modal-body">
                {{$modalMessage}}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{Lang::get('keys.close')}}</button>
            </div>
        </div>
    </div>
</div>