<table class="table">

    <?php $namespace = str_replace('\\', '', get_class($entity));?>
    @foreach($entity->documents as $document)
        <?php
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
    <tr>
        <td width="20%"><div class="file-icon @if (isset($size)) {{$size}}@endif"  data-type="{{$type}}"></div></td>
        <td>

            @if (!empty($document->url))
                <a href="{{$document->url}}">{{$document->title}} </a>
            @else
                <a class="doc-download" href="#">{{basename($document->path)}} </a> <span>(Документ завантажується до центральної бази даних)</span>
            @endif
        </td>
        <td>
            {{$document->updated_at}}
        </td>
        <td>
    </tr>
    @endforeach

</table>