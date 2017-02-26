
{{--HTML Template--}}
<script type="text/template" id="text-template-{{$namespace}}">
    <div class="file inline">
        <div class="col-md-4">
            @if (!empty($documentTypes)) @include('share.component.select-list-component',compact('documentTypes', 'inputName')) @endif
            @if ($namespace == 'bid' && is_object($entity->tender->procedureType) && $entity->tender->procedureType->procurement_method_type == 'aboveThresholdEU')
                <label class="hidden"><input type="checkbox" name="confidential"> Конфіденційно?</label>
                <div class="confidentialLabel confidentialCauseBlock hidden">
                    <input type="hidden" class="confidential" name="{{$inputName}}[confidential][]" value="0">
                    Причина: <input type="text" class="form-control confidentialCause"
                                    name="{{$inputName}}[confidentialCause][]">
                </div>
            @endif
        </div>
        <div class="col-md-6">
            <div class="file-description"></div>
            <input type="file" name="{{$inputName}}[files][]" class="form-control file-input" id="{{$namespace}}-0" data-index="0" data-namespace="{{$namespace}}">
        </div>
        <label for="{{$namespace}}-0" class="btn btn-success control-label"> {{Lang::get('keys.add_file')}}</label>
        <button class="btn btn-danger hidden control-label remove-file">{{Lang::get('keys.delete')}}</button>
    </div>
</script>
{{--HTML Template--}}





{{--section uploading file--}}
<div class="form-group">
    <div class="files {{$namespace}}">
        <div class="file inline">
            <div class="col-md-4">
                @if (!empty($documentTypes)) @include('share.component.select-list-component',compact('documentTypes')) @endif
                @if ($namespace == 'bid' && is_object($entity->tender->procedureType) && $entity->tender->procedureType->procurement_method_type == 'aboveThresholdEU')
                    <label class="confidentialLabel hidden"><input type="checkbox" name="confidential">
                        Конфіденційно?</label>
                    <div class="confidentialCauseBlock hidden">
                        <input type="hidden" class="confidential" name="{{$inputName}}[confidential][]" value="0">
                        Причина: <input type="text" class="form-control confidentialCause"
                                        name="{{$inputName}}[confidentialCause][]">
                    </div>
                @endif
            </div>
            <div class="col-md-6">
                <div class="file-description"></div>
                <input type="file" name="{{$inputName}}[files][]" class="form-control file-input" data-index="{{$index}}" data-namespace="{{$namespace}}" id="{{$namespace}}-{{$index}}">
            </div>
            <label for="{{$namespace}}-{{$index}}" class="btn btn-success control-label ">{{Lang::get('keys.add_file')}}</label>
            <button class="btn btn-danger hidden control-label remove-file">{{Lang::get('keys.delete')}}</button>
        </div>
    </div>
</div>
{{--section uploading file--}}

<div class="progress">
    <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
    </div>
</div>

