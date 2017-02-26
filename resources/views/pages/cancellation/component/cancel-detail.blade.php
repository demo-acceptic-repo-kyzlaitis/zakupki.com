<h4>Причина відміни</h4>
<p>{{$cancel->reason}}</p>
@if (isset($cancel) && $cancel->documents->count() > 0)
    <div class="row">
        <div class="col-md-12">
            <h4>Документи</h4>
            @include('share.component.document-list', ['entity' => $cancel, 'size' => 'file-icon-sm'])
        </div>
    </div>
@endif




