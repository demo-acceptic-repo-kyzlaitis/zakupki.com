{!! Form::hidden('tender_id', isset($tender) ? $tender->id : null, ['class' => 'form-control', 'placeholder' => '', 'required'])  !!}
    <!--<div class="form-group">
        <label for="reason" class="col-sm-4 control-label">Опис</label>

        <div class="col-lg-4">

            {!! Form::textArea('description', null, ['rows' => '10', 'class' => 'form-control', 'placeholder' => ''])  !!}
        </div>
    </div>-->


        {{--section uploading file--}}
        @include('share.component.add-file-component',['documentTypes' => []])
        {{--section uploading file--}}

<table  class="table table-striped table-bordered">
    @if (isset($contract))
        @foreach($contract->documents as $document)
            <tr>
                <td><span @if (empty($document->title)) data-toggle="tooltip" data-placement="bottom" title="Документ готується..." @endif> @if (empty($document->title)) {{basename($document->path)}} @else <a href="($document->url)}}">{{$document->title}} </a>@endif</span></td>

                <td>
                    @if ($contract->status != 'active')
                    <a data-href="{{route('contract.docs.delete', [$document->id])}}" href="#" data-toggle="modal" data-target="#deleteContractDocs" class="fileUpload btn btn-danger btn-xs">
                        <span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
                    </a>
                    @endif
                </td>
            </tr>
        @endforeach
    @endif
</table>


<!-- Modal -->
<div class="modal fade" id="deleteContractDocs">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Видалення файлу</h4>
            </div>
            <div class="modal-body">
                Ви справді хочете видалити файл?
            </div>
            <div class="modal-footer">
                <a href="#" type="button" class="btn-ok btn btn-danger">{{Lang::get('keys.ok')}}</a>
                <button type="button" class="btn btn-default" data-dismiss="modal">{{Lang::get('keys.cancel')}}</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(function(){
        $('[data-toggle="tooltip"]').tooltip();
        $('#deleteContractDocs').on('show.bs.modal', function(e) {
            $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
        });
    })
</script>

