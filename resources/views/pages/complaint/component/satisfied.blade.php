<?php
    $resolutionTypes = ['invalid' => 'Не дійсна', 'declined' => 'Отклонено', 'resolved' => 'Решено'];
?>
<h4>@if ($complaint->type == 'claim') Вимога @else Скарга @endif</h4><br>
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
<h4>Відповідь на прийняту скаргу</h4><br>
<div class="form-group">
    <label class="col-sm-4 control-label">Тип відповіді:</label>

    <div class="col-lg-8">
        <input type="radio" name="satisfied" checked id="satisfied" value="1"> <label for="satisfied">Скаргу задоволено.</label><br>
    </div>
</div>

<div class="form-group">
    <label for="reason" class="col-sm-4 control-label">Коментар:</label>

    <div class="col-lg-4">

        {!! Form::textarea('tenderer_action', null, ['class' => 'form-control', 'placeholder' => '', 'required', 'rows' => 5])  !!}
    </div>
</div>

@if(($complaint->status == 'satisfied' || $complaint->status == 'pending' || $complaint->status == 'accepted') && $complaint->type == 'complaint' && Auth::user()->organization->type == 'customer')
    @include('share.component.add-file-component', ['namespace' => 'complaint', 'index' => 0, 'inputName' => 'complaint'])
@endif

<hr>
<div class="form-group">
    <div class="col-lg-12 text-center">
        {!! Form::submit(Lang::get('keys.answer'),['class'=>'btn btn-danger']) !!}
    </div>
</div>

