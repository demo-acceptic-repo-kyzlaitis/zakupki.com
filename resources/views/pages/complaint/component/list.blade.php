    <table class="table table-striped">
        @foreach($complaints as $complaint)
            <tr data-complaint-id="{{$complaint->id}}">
                <td>
                    @if ($complaint->type == 'claim')
                        Вимога
                    @else
                        @if ($tender->procedureType->procurement_method_type == 'belowThreshold')
                            Звернення @else Скарга @endif
                    @endif</td>
                <td>{{$complaint->date_submitted}}</td>
                <td>{{$complaint->title}}</td>
                <td><span class="complaint-status label label-{{$complaint->statusDesc->style}}">{{$complaint->statusDesc->description}}</span></td>
                <td>@if (Auth::user()->organization->id == $complaint->tender_organization_id || Auth::user()->organization->id == $complaint->organization_id) <a href="{{route('complaint.edit', [$complaint->id])}}">Детальніше</a> @else <a href="{{route('complaint.show', [$complaint->id])}}">Детальніше</a> @endif</td>
                <td>
                    @if($complaint->canCancel())
                        <button type="button" class="btn btn-xs btn-danger complaint" data-complaint-id="{{$complaint->id}}"
                                @if($complaint->status == 'cancelled') disabled @endif data-toggle="modal" data-target="#complaint-modal-{{$complaint->id}}">
                            {{Lang::get('keys.cancel')}}
                        </button>
                        @include('pages.complaint.component.modal', ['id' => $complaint->id])
                    @endif
                </td>
            </tr>
        @endforeach{{--
        @foreach($entity->complaints as $complaint)
            <tr data-complaint-id="{{$complaint->id}}">
                <td>
                    @if ($complaint->type == 'claim')
                        Вимога
                    @else
                        @if ($tender->procedureType->procurement_method_type == 'belowThreshold')
                            Звернення @else Скарга @endif
                    @endif</td>
                <td>{{$complaint->date_submitted}}</td>
                <td>{{$complaint->title}}</td>
                <td><span class="complaint-status label label-{{$complaint->statusDesc->style}}">{{$complaint->statusDesc->description}}</span></td>
                <td>@if (Auth::user()->organization->id == $complaint->tender_organization_id || Auth::user()->organization->id == $complaint->organization_id) <a href="{{route('complaint.edit', [$complaint->id])}}">Детальніше</a> @else <a href="{{route('complaint.show', [$complaint->id])}}">Детальніше</a> @endif</td>
                <td>
                    @if($complaint->canCancel())
                        <button type="button" class="btn btn-xs btn-danger complaint" data-complaint-id="{{$complaint->id}}"
                                @if($complaint->status == 'cancelled') disabled @endif data-toggle="modal" data-target="#complaint-modal-{{$complaint->id}}">
                            Відхилити вимогу
                        </button>
                        @include('pages.complaint.component.modal', ['id' => $complaint->id])
                    @endif
                </td>
            </tr>
        @endforeach
        @if($entity->lots->count() > 0)
            @foreach($entity->lots as $lot)
                @foreach($lot->complaints as $complaint)
                    <tr data-complaint-id="{{$complaint->id}}">
                        <td>
                            @if ($complaint->type == 'claim')
                                Вимога
                            @else
                                @if ($tender->procedureType->procurement_method_type == 'belowThreshold')
                                    Звернення @else Скарга @endif
                            @endif</td>
                        <td>{{$complaint->date_submitted}}</td>
                        <td>{{$complaint->title}}</td>
                        <td>
                            <span class="complaint-status label label-{{$complaint->statusDesc->style}}">{{$complaint->statusDesc->description}}</span>
                        </td>
                        <td>@if (Auth::user()->organization->id == $complaint->tender_organization_id || Auth::user()->organization->id == $complaint->organization_id) <a href="{{route('complaint.edit', [$complaint->id])}}">Детальніше</a> @else <a href="{{route('complaint.show', [$complaint->id])}}">Детальніше</a> @endif</td>
                        <td>
                                @if($complaint->canCancel())
                                    <button type="button" class="btn btn-xs btn-danger complaint" data-complaint-id="{{$complaint->id}}"
                                            data-toggle="modal" data-target="#complaint-modal-{{$complaint->id}}">
                                        Відхилити вимогу
                                    </button>
                                    @include('pages.complaint.component.modal', ['id' => $complaint->id])
                                @endif
                            </td>
                    </tr>
                @endforeach
            @endforeach
        @endif
        @if ($entity->allBids->count())
            @foreach($entity->allBids as $bid)
                @if ($bid->qualification)
                    @foreach($bid->qualification->complaints as $complaint)
                        <tr data-complaint-id="{{$complaint->id}}">
                            <td>
                                @if ($complaint->type == 'claim')
                                    Вимога
                                @else
                                    @if ($bid->tender->procedureType->procurement_method_type == 'belowThreshold')
                                        Звернення @else Скарга @endif
                                @endif</td>
                            <td>{{$complaint->date_submitted}}</td>
                            <td>{{$complaint->title}}</td>
                            <td><span class="complaint-status label label-{{$complaint->statusDesc->style}}">{{$complaint->statusDesc->description}}</span></td>
                            <td>@if (Auth::user()->organization->id == $complaint->tender_organization_id || Auth::user()->organization->id == $complaint->organization_id) <a href="{{route('complaint.edit', [$complaint->id])}}">Детальніше</a> @else <a href="{{route('complaint.show', [$complaint->id])}}">Детальніше</a> @endif</td>
                            <td>
                                @if($complaint->canCancel())
                                    <button type="button" class="btn btn-xs btn-danger complaint" data-complaint-id="{{$complaint->id}}"
                                            data-toggle="modal" data-target="#complaint-modal-{{$complaint->id}}">
                                        Відхилити вимогу
                                    </button>
                                    @include('pages.complaint.component.modal', ['id' => $complaint->id])
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @endif
            @endforeach
        @endif
        @if ($entity->awards)
            @foreach($entity->awards as $award)
                @foreach($award->complaints as $complaint)
                    <tr data-complaint-id="{{$complaint->id}}">
                        <td>
                            @if ($complaint->type == 'claim')
                                Вимога
                            @else
                                @if ($tender->procedureType->procurement_method_type == 'belowThreshold')
                                    Звернення @else Скарга @endif
                            @endif</td>
                        <td>{{$complaint->date_submitted}}</td>
                        <td>{{$complaint->title}}</td>
                        <td><span class="complaint-status label label-{{$complaint->statusDesc->style}}">{{$complaint->statusDesc->description}}</span></td>
                        <td>@if (Auth::user()->organization->id == $complaint->tender_organization_id || Auth::user()->organization->id == $complaint->organization_id) <a href="{{route('complaint.edit', [$complaint->id])}}">Детальніше</a> @else <a href="{{route('complaint.show', [$complaint->id])}}">Детальніше</a> @endif</td>
                        <td>
                            @if($complaint->canCancel())
                                <button type="button" class="btn btn-xs btn-danger complaint" data-complaint-id="{{$complaint->id}}"
                                        data-toggle="modal" data-target="#complaint-modal-{{$complaint->id}}">
                                    Відхилити вимогу
                                </button>
                                @include('pages.complaint.component.modal', ['id' => $complaint->id])
                            @endif
                        </td>
                    </tr>
                @endforeach
            @endforeach
        @endif--}}
    </table>

