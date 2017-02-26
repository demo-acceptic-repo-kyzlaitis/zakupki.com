{!! Form::hidden('entity_id', isset($entity) ? $entity->id : null, ['class' => 'form-control', 'placeholder' => '', 'required'])  !!}
{!! Form::hidden('entity_type', isset($entity) ? $entity->type : null, ['class' => 'form-control', 'placeholder' => '', 'required'])  !!}
    <div class="form-group">
        <label for="reason" class="col-sm-4 control-label">Причина</label>

        <div class="col-lg-4">

            {!! Form::textarea('reason', null, ['class' => 'form-control', 'placeholder' => '', 'required', 'rows' => 5])  !!}
        </div>
    </div>


    @if (isset($cancel) && $cancel->documents->count() > 0)
        <div class="row">
            <div class="col-md-12">
                <h4>Документи</h4>
                @include('share.component.document-list', ['entity' => $cancel, 'size' => 'file-icon-sm', 'delete' => false])
            </div>
        </div>
        @endif
{{--section uploading file--}}
@include('share.component.add-file-component',['documentTypes' => [], 'index' => 1, 'namespace' => 'cancel', 'inputName' => 'cancel'])
{{--section uploading file--}}




