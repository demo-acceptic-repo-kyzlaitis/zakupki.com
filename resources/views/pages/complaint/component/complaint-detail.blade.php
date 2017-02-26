<?php
$resolutionTypes = ['invalid' => 'Не дійсно', 'declined' => 'Відхилено', 'resolved' => 'Вирішено'];
?>
<div class="col-md-12">
    <h4>@if ($complaint->type == 'claim' || $complaint->type == 'answered') Вимога @else Скарга  @endif @if ($complaint->complaintable->type == 'tender') про виправлення умов
        закупівлі @elseif ($complaint->complaintable->type == 'lot') про виправлення умов лоту @elseif($complaint->complaintable->type == 'award') на
         визначення переможця @else на прекваліфікацію @endif
        <span class="label label-{{$complaint->statusDesc->style}}">{{$complaint->statusDesc->description}}</span>

        @if ($complaint->canComplaint())
            <a class="btn btn-sm btn-danger pull-right" href="{{route('claim.complaint', [$complaint->id])}}">{{Lang::get('keys.change_in_complaint')}}</a>
        @endif
    </h4><br>
    <table class="clean-table">
        <tr>
            <th>Заголовок:</th>
            <td>{{$complaint->title}}</td>
        </tr>
        <tr>
            <th>Вимога:</th>
            <td>{{$complaint->description}}</td>
        </tr>
        <tr>
            <th>Дата подачі:</th>
            <td>{{$complaint->date_submitted}}</td>
        </tr>
        @if (isset($complaint) && $complaint->documents()->complainter()->count())

            <tr>
                <th>Документи:</th>
                <td>
                    @include('pages.complaint.component.document-list', ['author' => 'complaint_owner', 'entity' => $complaint, 'size' => 'file-icon-sm'])
                </td>
            </tr>
        @endif

    </table>

    @if (!empty($complaint->resolution_type))
        <hr>
        <h4>Відповідь організатора</h4>
        <table class="clean-table">
            <tr>
                <th>Тип відповіді:</th>
                <td>{{$resolutionTypes[$complaint->resolution_type]}}</td>
            </tr>
            <tr>
                <th>Відповідь:</th>
                <td>{{$complaint->resolution}}</td>
            </tr>
            <tr>
                <th>Дата відповіді:</th>
                <td>{{$complaint->date_answered}}</td>
            </tr>
            @if (isset($complaint) && $complaint->documents()->tenderer()->count() > 0)
                <tr>
                    <th>Документи:</th>
                    <td>
                        @include('pages.complaint.component.document-list', ['author' => 'tender_owner', 'entity' => $complaint, 'size' => 'file-icon-sm'])
                    </td>
                </tr>
            @endif
        </table>
    @endif

    @if ($complaint->satisfied !== null)
        @if ($complaint->satisfied)
            <hr>
            <h4>Вимогу задоволено</h4>
            <table class="clean-table">
                <tr>
                    <th>Коментар:</th>
                    <td>{{$complaint->tenderer_action}}</td>
                </tr>
<!--                 @if (isset($complaint) && $complaint->documents()->tenderer()->count() > 0) -->
<!--                     <tr> -->
<!--                         <th>Документи:</th> -->
<!--                         <td> -->
<!--                             @include('pages.complaint.component.document-list', ['author' => 'complaint_owner', 'entity' => $complaint, 'size' => 'file-icon-sm']) -->
<!--                         </td> -->
<!--                     </tr> -->
<!--                 @endif -->
            </table>


        @else
            <hr>
            <h4>Вимогу передано в орган оскарження</h4>
            <table class="clean-table">
                <tr>
                    <th>Дата передачі:</th>
                    <td>{{$complaint->date_escalated}}</td>
                </tr>
                <tr>
                    <th>Коментар:</th>
                    <td>{{$complaint->tenderer_action}}</td>
                </tr>
            </table>
        @endif
    @endif

    @if ($complaint->status == 'stopping')
        <hr>
        <h4>Вимогу відмінено скаржником</h4>
        <table class="clean-table">
            <tr>
                <th>Причина:</th>
                <td>{{$complaint->cancellation_reason}}</td>
            </tr>
        </table>

    @endif

    @if((in_array($complaint->status, ['pending', 'accepted', 'satisfied']) && $complaint->type == 'complaint' &&  Auth::user()->organization->type == 'supplier') )
    {!! Form::model($complaint,['route' => ['complaint.update', $complaint->id],
                        'method'=>'PUT',
                        'enctype'=>'multipart/form-data',
                        'class'=>'form-horizontal',]) !!}

        @include('share.component.add-file-component', ['namespace' => 'complaint', 'index' => 0, 'inputName' => 'complaint'])

    <div class="form-group">
        <div class="col-lg-12 text-center">
            {!! Form::submit(Lang::get('keys.download'),['class'=>'btn btn-success']) !!}
        </div>
    </div>

    {!! Form::close() !!}
    @endif
</div>