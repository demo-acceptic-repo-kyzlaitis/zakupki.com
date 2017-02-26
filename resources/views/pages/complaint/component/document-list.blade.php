<table class="table">

    <?php $namespace = str_replace('\\', '', get_class($entity));?>
    @foreach($entity->documents as $document)
        @if ($document->author == $author)
            <tr>
                <td width="20%"><div class="file-icon @if (isset($size)) {{$size}}@endif"  data-type="{{isset(pathinfo(basename($document->path))['extension']) ? pathinfo(basename($document->path))['extension'] : pathinfo(basename($document->title))['extension']}}"></div></td>
                <td>
                    @if (!empty($document->url)) <a  href="{{$document->url}}">{{$document->title}}</a> @else <a href="{{route('document.download', [$document->id])}}">{{basename($document->path)}}</a> @endif<br>
                </td>
                <td>
                    {{$document->created_at}}
                </td>
                <td></td>
            </tr>
        @endif
    @endforeach
</table>