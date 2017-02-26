@extends('layouts.index')

@section('content')
    @include('share.component.modal-confirm', ['modalNamespace' => 'tender', 'modalTitle' => 'Видалення тендеру', 'modalMessage' => 'Ви справді хочете видалити тендер?'])
    <div class="container">
        <h2>Вимоги/Скарги</h2><hr>
        @if($complaints->count())
            <div class="row">
                <table  class="table table-striped table-bordered">
                    <tr>
                        <th>Тип</th>
                        <th>Дата подачі</th>
                        <th>Тендер</th>
                        <th>Назва</th>
                        <th>Статус</th>
                        <th></th>
                    </tr>
                        @foreach($complaints as $complaint)
                        <?php
                            if ($complaint->organization_id != Auth::user()->organization->id && $complaint->status == 'draft') {
                                continue;
                            }
                        if ($complaint->complaintable->type == 'qualification') {
                            $tender = $complaint->complaintable->bid->tender;
                        } else {
                            $tender = $complaint->complaintable->tender;
                        }
                        ?>
                            <tr>
                                <td>
                                    @if ($complaint->type == 'claim')
                                        @if ($tender->procedureType->procurement_method_type == 'belowThreshold')
                                            Звернення @else Вимога @endif
                                    @else
                                        Скарга
                                    @endif
                                    @if ($complaint->complaintable->type == 'tender' || $complaint->complaintable->type == 'lot')
                                        на умови @else на кваліфікацію @endif
                                </td>
                                <td>{{$complaint->date_submitted}}</td>
                                <td><a href="{{route('tender.show', [$tender->id])}}">{{$tender->title}}</a></td>
                                <td>{{$complaint->title}}</td>
                                <td><span class="label label-{{$complaint->statusDesc->style}}">{{$complaint->statusDesc->description}}</span></td>
                                <td>
                                    @if (Auth::user()->organization->id == $complaint->organization_id)
                                        @if (($complaint->status == 'draft' || $complaint->status == 'answered'))
                                            <a href="{{route('complaint.edit', [$complaint->id])}}" class="btn btn-xs btn-danger helper" title-data="Ok"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span></a>
                                        @endif
                                    @else
                                        @if ($complaint->status == 'claim' && $tender->status != 'unsuccessful' && ($tender->procedureType->procurement_method_type != 'aboveThresholdEU' || $tender->status == 'active.pre-qualification.stand-still'))
                                            <a href="{{route('complaint.edit', [$complaint->id])}}" class="btn btn-xs btn-danger helper" title-data="Ok"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span></a>
                                        @endif
                                        @if ($complaint->type == 'complaint' && $complaint->status == 'satisfied')
                                                <a href="{{route('complaint.edit', [$complaint->id])}}" class="btn btn-xs btn-danger  helper" title-data="Ok"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span></a>

                                                @endif
                                    @endif
                                    <a href="{{route('complaint.show', [$complaint->id])}}"
                                       class="btn btn-xs btn-warning helper" title-data="Пошук"><span class="glyphicon glyphicon glyphicon-search"
                                                                            aria-hidden="true"></span></a>
                                        @if($complaint->canCancel())
                                            <button type="button" class="btn btn-xs btn-danger complaint" data-complaint-id="{{$complaint->id}}"
                                                    @if($complaint->status == 'cancelled') disabled @endif data-toggle="modal" data-target="#complaint-modal-{{$complaint->id}}">
                                                {{Lang::get('keys.cancel_claim')}}
                                            </button>
                                            @include('pages.complaint.component.modal', ['id' => $complaint->id])
                                        @endif

                                </td>
                            </tr>
                        @endforeach
                </table>
            </div>

            <div class="text-center">
                {!! $complaints->render() !!}
            </div>
        @endif
    </div>
@endsection