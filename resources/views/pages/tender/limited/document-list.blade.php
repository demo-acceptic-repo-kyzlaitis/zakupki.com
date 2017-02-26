<table class="table">

    @foreach($entity->documents as $document)
        <tr>
            <td width="20%"><div class="file-icon @if (isset($size)) {{$size}}@endif"  data-type="{{pathinfo(basename($document->path))['extension']}}"></div></td>
            <td>
                @if (!empty($document->url)) <a  href="{{$document->url}}">{{$document->title}}</a> @else <a href="{{route('document.download', [$document->id])}}">{{basename($document->path)}}</a> @endif<br>
            </td>
            <td>
                {{$document->updated_at}}
            </td>
        </tr>
    @endforeach
</table>