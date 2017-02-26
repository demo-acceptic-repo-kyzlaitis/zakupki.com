@extends('layouts.admin')

@section('content')
    <table class="table table-striped">
        <input type="text" hidden value="{{csrf_token()}}" name="_token">
        <thead>
        <tr>
            <th>Текст</th>
            <th>Действия</th>
            <th>Имя файла</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            @foreach($files as $file)
                <tr>
                    <td width="50%;">
                        <textarea name="" id="file-template-content" cols="20" rows="5" class="form-control double-click" style="resize: none;" readonly>{{ $file->getContents()}}</textarea>
                    </td>
                    <td style="display: inline-block;vertical-align: middle;float: none;">
                        <div class="btn-group center-block" role="group" aria-label="group one">
                            <button type="button" class="btn btn-primary" id="save-email-template" data-path="{{$file->getRelativePathname()}}">{{Lang::get('keys.save_with_backup')}}</button>
                            <button type="button" class="btn btn-default" id="restore-copy" data-path="{{$file->getRelativePathname()}}">{{Lang::get('keys.restore_file')}}</button>
                        </div>
                    </td>
                    <td>
                        {{$file->getRelativePathname()}}
                    </td>
                </tr>
            @endforeach
        </tr>
        </tbody>
    </table>
@endsection