<select name="{{$inputName}}[docTypes][]" required class="form-control hidden">
    @foreach($documentTypes as $doc)
        <?php if (isset($tender) && ($tender->procedureType->procurement_method_type == 'competitiveDialogueUA' || $tender->procedureType->procurement_method_type == 'competitiveDialogueEU')):?>
                <?php if ($doc->document_type != 'commercialProposal' && $doc->document_type != 'billOfQuantity'):?>
                        <option value="{{$doc->id}}">{{$doc->lang_ua}}</option>
                <?php endif;?>
        <?php else:?>
                <option value="{{$doc->id}}">{{$doc->lang_ua}}</option>
        <?php endif;?>
    @endforeach
    @if (is_object($procedureType))
        <option value="0">Інші</option>
    @endif
</select>