<?php
    $resolutionTypes = ['invalid' => 'Не дійсна', 'declined' => 'Відхилено', 'resolved' => 'Вирішено'];
?>
<h4>Вимога</h4><br>
<table class="clean-table">
    <tr>
        <th>Заголовок:</th>
        <td>{{$complaint->title}}</td>
    </tr>
    <tr>
        <th>Суть вимоги:</th>
        <td>{{$complaint->description}}</td>
    </tr>
    <tr>
        <th>Документи:</th>
        <td>
            @if (isset($complaint) && $complaint->documents()->complainter()->count())
                @include('pages.complaint.component.document-list', ['author' => 'complaint_owner', 'entity' => $complaint, 'size' => 'file-icon-sm'])
            @endif
        </td>
    </tr>
</table>
<hr>
<h4>Рішення організатора</h4><br>
    <table class="clean-table">
        <tr>
            <th>Тип відповіді:</th>
            <td>@if (isset($resolutionTypes[$complaint->resolution_type])) {{$resolutionTypes[$complaint->resolution_type]}} @endif </td>
        </tr>
        <tr>
            <th>Відповідь:</th>
            <td>{{$complaint->resolution}}</td>
        </tr>
        @if (isset($complaint) && $complaint->documents()->tenderer()->count())
        <tr>
            <th>Документи:</th>
            <td>

                    @include('pages.complaint.component.document-list', ['author' => 'tender_owner', 'entity' => $complaint, 'size' => 'file-icon-sm', 'delete' => true, 'route' => 'complaint.edit'])

            </td>
        </tr>
        @endif
    </table>
<hr>

@if ($tender->procedureType->procurement_method_type != 'competitiveDialogueEU' && $tender->procedureType->procurement_method_type != 'competitiveDialogueUA')
    <h4>Відповідь на рішення</h4><br>
    <div class="form-group">
        <label class="col-sm-4 control-label">Тип відповіді:</label>

        <div class="col-lg-8">

            <input type="radio" name="satisfied" id="satisfied" value="1"> <label for="satisfied">Вимогу задоволено.</label><br>
            <input type="radio" name="satisfied" id="not_satisfied" value="0"> <label for="not_satisfied">Вимогу не задоволено. Перевести вимогу в скаргу для розгляду органу оскарження.</label><br>
        </div>
    </div>

    <div class="form-group">
        <label for="reason" class="col-sm-4 control-label">Коментар:</label>

        <div class="col-lg-4">

            {!! Form::textarea('tenderer_action', null, ['class' => 'form-control', 'placeholder' => '', 'required', 'rows' => 5])  !!}
        </div>
    </div>

    <hr>
    <div class="form-group">
        <div class="col-lg-12 text-center">
            {!! Form::submit(Lang::get('keys.answer'),['class'=>'btn btn-danger']) !!}
        </div>
    </div>
@endif
