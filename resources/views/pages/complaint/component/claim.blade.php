<h4>Вимога @if ($complaint->complaintable->type == 'tender') про виправлення умов закупівлі @else на кваліфікацію @endif</h4><br><br>
<table class="clean-table">
    <tr>
        <th>Заголовок:</th>
        <td>{{$complaint->title}}</td>
    </tr>
    <tr>
        <th>Суть вимоги:</th>
        <td>{{$complaint->description}}</td>
    </tr>
    <tr>
        <th>Документи:</th>
        <td>
            @if (isset($complaint) && $complaint->documents()->complainter()->count())
                @include('pages.complaint.component.document-list', ['author' => 'complaint_owner', 'entity' => $complaint, 'size' => 'file-icon-sm'])
            @endif
        </td>
    </tr>
</table>
<hr>
<h4>Відповідь на вимогу</h4><br>
<div class="form-group">
    <label for="reason" class="col-sm-4 control-label">Тип відповіді:</label>

    <div class="col-lg-4">

        {!! Form::select("resolution_type", ['invalid' => 'Не задоволено', 'declined' => 'Відхилено', 'resolved' => 'Задоволено'], null,  ['class' => 'form-control']) !!}
    </div>
</div>

<div class="form-group">
    <label for="reason" class="col-sm-4 control-label">Відповідь на вимогу:</label>

    <div class="col-lg-4">

        {!! Form::textarea('resolution', null, ['class' => 'form-control', 'placeholder' => '', 'required', 'rows' => 5])  !!}
    </div>
</div>
@if (isset($complaint) && $complaint->documents()->tenderer()->count())
<div class="col-md-12">
    <h4>Документи</h4>
    @include('pages.complaint.component.document-list', ['author' => 'tender_owner', 'entity' => $complaint, 'size' => 'file-icon-sm', 'delete' => true, 'route' => 'complaint.edit'])
    <hr>
</div>
@endif

{{--section uploading file--}}
@include('share.component.add-file-component',['documentTypes' => [], 'index' => 1, 'namespace' => 'complaint', 'inputName' => 'complaint'])
{{--section uploading file--}}

<hr>
<div class="form-group">
    <div class="col-lg-12 text-center">
        {!! Form::submit(Lang::get('keys.answer'),['class'=>'btn btn-danger']) !!}
    </div>
</div>

