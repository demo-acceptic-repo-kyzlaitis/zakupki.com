<!-- Modal -->
<div class="modal fade" id="complaint-modal-{{$id}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        {!! Form::open(['url' => '/complaint/cancel', 'method'=>'POST', 'id' => 'cancel-complaint-form']) !!}
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Відкликати вимогу?</h4>
            </div>
            <div class="modal-body">
                Причина відкликання
                <input type="hidden" name="complaintId" value="{{$id}}">
                {!! Form::textarea('cancellation_reason', null, ['class' => 'form-control', 'placeholder' => '', 'required', 'rows' => 5])  !!}

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{Lang::get('keys.no')}}</button>
                <button type="submit" class="btn btn-primary" onclick="$('#complaint-modal-{{$id}}').modal('hide')">{{Lang::get('keys.yes')}}</button>
            </div>
        </div>
        {!! Form::close() !!}
    </div>
</div>