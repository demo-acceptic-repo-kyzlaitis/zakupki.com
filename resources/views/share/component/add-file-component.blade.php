<?php $entityName = $namespace;
if ($namespace != 'tender' && strlen($namespace) == 6)
    $entityName = 'lot';
if ($namespace != 'tender' && strlen($namespace) == 14)
    $entityName = 'item';?>
{{--HTML Template--}}
<script type="text/template" id="text-template-{{$namespace}}">
    <div class="file inline">
        <div class="col-md-4">
            @if (!empty($documentTypes)) @include('share.component.select-list-component',compact('documentTypes', 'inputName')) @endif
            @if ($namespace == 'bid')
                @if (isset($entity) && is_object($entity->tender->procedureType) && $entity->tender->procedureType->procurement_method_type == 'competitiveDialogueUA' || $entity->tender->procedureType->procurement_method_type == 'competitiveDialogueEU')
                    <label class="descriptionDecisionLabel hidden">
                        <input type="checkbox" name="is_description_decision ">
                        Опис рішення про закупівлю
                    </label>
                @endif
                @if (isset($entity) && is_object($entity->tender->procedureType) && $entity->tender->procedureType->procurement_method_type == 'aboveThresholdEU' || $entity->tender->procedureType->procurement_method_type == 'competitiveDialogueUA' || $entity->tender->procedureType->procurement_method_type == 'competitiveDialogueEU' || $entity->tender->procedureType->procurement_method == 'selective')
                    <label class="confidentialLabel hidden"><input type="checkbox" name="confidential">
                        Конфіденційно?
                    </label>
                    <div class="confidentialCauseBlock hidden">
                        <input type="hidden" class="confidential" name="{{$inputName}}[confidential][]" value="0">
                        Причина: <input type="text" class="form-control confidentialCause"
                                        name="{{$inputName}}[confidentialCause][]">
                    </div>
                @endif
            @endif
        </div>
        <div class="col-md-6">
            <div class="file-description"></div>
            <input type="file" name="{{$inputName}}[files][]" class="form-control" id="{{$namespace}}-0" data-index="0" data-namespace="{{$namespace}}" style="display:none;">
        </div>
        <label for="{{$namespace}}-0" class="btn btn-success control-label add-file" data-entity="{{$entityName}}"
               title="Зверніть увагу, що об'єм одного файлу не повинен перевищувати 45 Мб"> {{Lang::get('keys.add_file')}}</label>
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
                @if ($namespace == 'bid')
                    @if (isset($entity) && is_object($entity->tender->procedureType) && $entity->tender->procedureType->procurement_method_type == 'competitiveDialogueUA' || $entity->tender->procedureType->procurement_method_type == 'competitiveDialogueEU')
                        <label class="descriptionDecisionLabel hidden">
                            <input type="checkbox" name="{{$inputName}}[description_decision][] ">
                            Опис рішення про закупівлю
                        </label>
                    @endif
                    @if (isset($entity) && is_object($entity->tender->procedureType) && $entity->tender->procedureType->procurement_method_type == 'aboveThresholdEU' || $entity->tender->procedureType->procurement_method_type == 'competitiveDialogueEU' || $entity->tender->procedureType->procurement_method_type == 'competitiveDialogueEU.stage2')
                        <label class="confidentialLabel hidden"><input type="checkbox" name="confidential">
                            Конфіденційно?
                        </label>
                        <div class="confidentialCauseBlock hidden">
                            <input type="hidden" class="confidential" name="{{$inputName}}[confidential][]" value="0">
                            Причина: <input type="text" class="form-control confidentialCause"
                                            name="{{$inputName}}[confidentialCause][]">
                        </div>
                    @endif
                @endif
            </div>
            <div class="col-md-6">
                <div class="file-description"></div>
                <input type="file" name="{{$inputName}}[files][]" class="form-control" data-index="{{$index}}" data-namespace="{{$namespace}}" id="{{$namespace}}-{{$index}}" style="display:none;">
            </div>
            <label for="{{$namespace}}-{{$index}}" class="btn btn-success control-label add-file" data-entity="{{$entityName}}"
                   title="Зверніть увагу, що об'єм одного файлу не повинен перевищувати 45 Мб">{{Lang::get('keys.add_file')}}</label>
            <button class="btn btn-danger hidden control-label remove-file">{{Lang::get('keys.delete')}}</button>
        </div>
    </div>
</div>
<script type="text/javascript" src="/js/jquery-asProgress.min.js"></script>
<script type="text/javascript">
    
</script>
{{--section uploading file--}}
