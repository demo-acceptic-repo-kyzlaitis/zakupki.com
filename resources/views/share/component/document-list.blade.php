<table class="table">

    <?php $namespace = str_replace('\\', '', get_class($entity));?>

    <?php $documents = $entity->documents->sortBy(function ($document) {
        $time = 10000000000 - strtotime($document['created_at']);
        return bin2hex($document['orig_id']) . $time;
    });?>
    @foreach($documents as $document)
        <?php
        if ((isset($documentTypes[$document->type_id]) && $documentTypes[$document->type_id] == 'protocol') || (isset($documentTypes[$document->type_id]) && $documentTypes[$document->type_id] == 'digital_signature')) {
            continue;
        }
                if (($document->type_id == 27 || $document->type_id == 29) && $tender->status == 'active.pre-qualification') {
                    continue;
                }
        if (isset(pathinfo(basename($document->path))['extension'])) {
            $type = pathinfo(basename($document->path))['extension'];
        } elseif (isset(pathinfo(basename($document->title))['extension'])) {
            $type = pathinfo(basename($document->title))['extension'];
        } else {
            $types = [
                    "application/vnd.openxmlformats-officedocument" => 'doc',
                    "text/plain" => 'txt',
                    "application/x-zip-compressed" => 'zip',
                    "application/pdf" => 'pdf',
                    "text/html" => 'xml',
                    "application/msword" => 'doc',
                    "image/png" => 'png',
                    "image/jpeg" => 'jpg',
                    "application/vnd.ms-excel" => 'xls',
                    "text/richtext" => 'doc',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'doc'
            ];
            if (isset($types[$document->format])) {
                $type = $types[$document->format];
            } else {
                $type = 'data';
            }

        }
        ?>
        <tr @if ($document->status == 'old') class="tender-document-old" @endif>
            <td width="20%">
                @if ($document->status != 'old')
                    <div class="file-icon @if (isset($size)) {{$size}}@endif" data-type="{{$type}}"></div>
                @endif
            </td>
            <td>
                <?php
                    $canDownload = false;
                    if ((Auth::check() && Auth::user()->organization->id == $entity->organization_id) || $tender->isOwner() || !$document->confidential) {
                        $canDownload = true;
                    }
                ?>

                @if (!empty($document->url))
                    @if($canDownload)
                        @if ($document->confidential || ($namespace == 'AppModelBid' && $entity->canDownloadDocsFromUs()) /*|| $tender->status == 'active.tendering'*/)
                            <a href="{{route('bid.download', [$document->id])}}">{{$document->title}}</a>
                        @else
                            <a href="{{$document->url}}">{{$document->title}} </a>
                        @endif
                    @else
                        {{$document->title}}
                    @endif
                @else
                    @if($canDownload)
                        <a class="doc-download" href="#">{{basename($document->path)}} </a> <span>(Документ завантажується до центральної бази даних)</span>
                    @else
                        {{basename($document->path)}} <span>(Документ завантажується до центральної бази даних)</span>
                    @endif
                @endif

                @if ($document->confidential)
                    <br> Конфіденційно! Причина: {{$document->confidential_cause}}
                @endif

                @if ($document->type)
                    <br>Тип документу: {{$document->type->lang_ua}}
                @endif
            </td>
            <td>
                @if(strtotime($document->date_modified) > 1000){{$document->date_modified}}@else {{$document->created_at}}@endif
            </td>
            <td>
            @if (((isset($delete) && $delete) || (isset($edit) && $edit)) && ($document->status != 'old'))
                <td>
                    @if (isset($edit) && $edit)
                        <div href="#" class="fileUpload btn btn-danger btn-xs helper" title-data="Редагування">
                            <span class="glyphicon glyphicon-pencil " aria-hidden="true"></span>
                            <input type="file" name="newfiles[{{$document->id}}]" class="upload"/>
                        </div>
                    @endif

                    @if (isset($delete) && $delete)
                        <a data-href="{{route('bid.docs.destroy', [$document->id])}}" href="#" data-toggle="modal" data-target="#delete{{$namespace}}"
                           class="fileUpload btn btn-danger btn-xs">
                            <span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
                        </a>
                        @include('share.component.modal-confirm', ['modalNamespace' => $namespace, 'modalTitle' => 'Видалення файлу', 'modalMessage' => 'Ви справді хочете видалити файл?'])
                    @endif
                </td>
            @endif
        </tr>
    @endforeach
        <?php
        if(isset($notLoadDocuments) && $notLoadDocuments != 0){?>
            <script>
                function hide(){
                    $('.hide_batton').attr('disabled','disabled');
                }
                hide();
                setTimeout(function(){location.reload(); }, 5000);
            </script>
        <?php
          }?>

</table>