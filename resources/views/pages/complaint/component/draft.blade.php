<div style="float: right">
    @if (isset($complaint))
        @if ($entity->tender->procedureType->threshold_type == 'below' || $entity->tender->procedureType->threshold_type == 'below.limited')
            <a class="btn btn-danger" href="{{route('claim.claim', [$complaint->id])}}">{{Lang::get('keys.publish_claim')}}</a>
        @elseif ($entity->tender->procedureType->threshold_type == 'above' && ($complaint->complaintable->type == 'tender' || $complaint->complaintable->type == 'lot' || $complaint->complaintable->type == 'award'))
            <div class="btn-group">
                <button type="button" class="btn btn-danger dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    {{Lang::get('keys.publish')}} <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                    <li><a href="{{route('claim.claim', [$complaint->id])}}">Вимогу</a></li>
                    <li><a href="{{route('claim.complaint', [$complaint->id])}}">Скаргу</a></li>
                </ul>
            </div>
        @elseif ($entity->tender->procedureType->threshold_type == 'above.limited' || $entity->tender->procedureType->threshold_type == 'above')
            <a class="btn btn-danger" href="{{route('claim.complaint', [$complaint->id])}}">{{Lang::get('keys.publish_complaint')}}</a>
        @endif

    @endif

</div>


<h4>Вимога/Скарга @if ($entity->type == 'tender' || $entity->type == 'lot') про виправлення умов
    закупівлі @else на
    кваліфікацію @endif</h4><br>
{!! Form::hidden('entity_id', isset($entity) ? $entity->id : null, ['class' => 'form-control', 'placeholder' => '', 'required'])  !!}
{!! Form::hidden('entity_type', isset($entity) ? $entity->type : null, ['class' => 'form-control', 'placeholder' => '', 'required'])  !!}



<div class="form-group">
    <label for="reason" class="col-sm-4 control-label">Заголовок</label>

    <div class="col-lg-4">

        {!! Form::text('title', null, ['class' => 'form-control', 'placeholder' => '', 'required'])  !!}
    </div>
</div>

<div class="form-group">
    <label for="reason" class="col-sm-4 control-label">Суть вимоги</label>

    <div class="col-lg-4">

        {!! Form::textarea('description', null, ['class' => 'form-control', 'placeholder' => '', 'required', 'rows' => 5])  !!}
    </div>
</div>


@if (isset($complaint) && $complaint->documents->count() > 0)
    <div class="row">
        <div class="col-md-12">
            <h4>Документи</h4>
            @include('share.component.document-list', ['entity' => $complaint, 'size' => 'file-icon-sm', 'delete' => true, 'route' => 'complaint.edit'])
        </div>
    </div>
@endif
{{--section uploading file--}}
@include('share.component.add-file-component',['documentTypes' => [], 'index' => 1, 'namespace' => 'complaint', 'inputName' => 'complaint'])
{{--section uploading file--}}

<hr>
<div class="form-group">
    <div class="col-lg-12 text-center">
        {!! Form::submit(isset($complaint) ? Lang::get('keys.cancel') : Lang::get('keys.create'),['class'=>'btn btn-info']) !!}
    </div>
</div>




