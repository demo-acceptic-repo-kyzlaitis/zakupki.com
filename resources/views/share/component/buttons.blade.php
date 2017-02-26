<div class="text-right" style="position: relative;">
    <div style="position: absolute; right: 0">

        @if (Auth::check())
            @if (isset($tender->stages) && isset($tender->stages->firstStage) && $tender->stages->firstStage->cbd_id != $tender->cbd_id)
                <a class="btn btn-info" href="{{route('tender.show', $tender->stages->firstStage->id)}}">{{Lang::get('keys.first_stage')}}</a>
            @elseif (isset($tender->stages) && isset($tender->stages->secondStage) && $tender->stages->secondStage->cbd_id != $tender->cbd_id)
                <a class="btn btn-info" href="{{route('tender.show', $tender->stages->secondStage->id)}}">{{Lang::get('keys.second_stage')}}</a>
            @endif

            @if (Auth::check() && $tender->canBid() && !$tender->isOwner(Auth::user()->id) && !$tender->multilot && Auth::user()->organization->type == 'supplier')
                <?php
                $bids = $tender->bids()->get();
                $hasBid = false;
                foreach ($bids as $bid) {
                    if ($hasBid = $bid->isOwner(Auth::user()->id)){
                        break;
                    }
                }
                $bidRemoved = isset($bidRemoved); //bid controller line 304
                ?>
                @if ($bids->count() > 0 && $hasBid && !$bidRemoved)
                    <a href="{{route('bid.edit', [$bid->id])}}" class="btn btn-success">{{Lang::get('keys.edit_bid')}}</a>
                @elseif ($tender->status != 'active.qualification')
                    <a href="{{route('bid.new', ['tender', $tender->id])}}" class="btn btn-success">{{Lang::get('keys.create_bid')}}</a>
                @endif
            @endif
            @if ($tender->canQuestion() && !$tender->isOwner(Auth::user()->id) && Auth::user()->organization->type == 'supplier')
                <a href="{{route('questions.create', ['tender', $tender->id])}}" class="btn btn-success">{{Lang::get('keys.create_question')}}</a>
            @endif

            @if (Auth::check() && $tender->isOwner(Auth::user()->id))
                @if ((($tender->canEdit()  && $tender->isPublished()) || $tender->canSign()) && $tender->signed == 0 && $tender->status != 'draft.stage2' && $tender->status != 'draft')
                    <a href="#" class="btn btn-success" data-toggle="modal" data-target="#signature-set" data-id="{{$tender->id}}" data-documentname="tender" >{{Lang::get('keys.sign_ecp')}}</a>
                @endif
                @if ($tender->canPublish())
                    @if (Auth::user()->organization->mode == 1 && $tender->mode == 0)
                        <a data-href="{{route('tender.publish', [$tender->id])}}" href="#" data-toggle="modal" data-target="#deletepublish{{$tender->id}}"
                           class="btn btn-info">{{Lang::get('keys.publish')}}</a>
                    @else
                        <a href="{{route('tender.publish', [$tender->id])}}" class="btn btn-info">{{Lang::get('keys.publish')}}</a>
                    @endif
                @endif
                @if ($tender->status == 'draft.stage2')
                    <a href="{{route('tender.publishSecondStage', [$tender->id])}}" class="btn btn-info">{{Lang::get('keys.publish')}}</a>
                @endif
                @if ($tender->canEdit())
                    <a href="{{route('tender.edit', [$tender->id])}}" class="btn btn-info">{{Lang::get('keys.cancel')}}</a>
                @endif
                @if ($tender->canQualify())
                    @if ($tender->procedureType->procurement_method == 'limited')
                        @if ($tender->procedureType->procurement_method_type == 'reporting')
                            @if (!$tender->award)
                                <a href="{{route('award.define', ['tender' => $tender->id])}}" class="btn btn-warning">{{Lang::get('keys.winner_determine')}}</a>
                            @elseif ($tender->award->status == 'pending')
                                <a href="{{route('award.edit', [$tender->award->id])}}" class="btn btn-warning">{{Lang::get('keys.winner_determine')}}</a>
                            @endif
                        @else
                            <a href="{{route('award.list', ['tender' => $tender->id])}}" class="btn btn-warning">{{Lang::get('keys.customer_determine')}}</a>
                        @endif
                    @else
                        {{--скрывает кнопку квалификации если есть скарга в статусе accpted--}}
                        @if(!$tender->hasAnyAcceptedComplaints() && $tender->isOpen())
                            <a href="{{route('award.tender', [$tender->id])}}" class="btn btn-warning">{{Lang::get('keys.qualification')}}</a>
                        @elseif(!$tender->isOpen())
                            <a href="{{route('award.tender', [$tender->id])}}" class="btn btn-warning">{{Lang::get('keys.qualification')}}</a>
                        @endif
                    @endif
                @endif
                @if ($tender->status == 'active.pre-qualification' && $tender->isOwner())
                    <a href="{{route('bid.qualifications', [$tender->id])}}" class="btn btn-warning">{{Lang::get('keys.prequalification')}}</a>
                @endif

                @if ($tender->canCancel() && !$tender->cancel && ($tender->canCancelAfterComplaintEndDate() || !$tender->hasPendingComplaints()) && !$tender->isStandStill())
                    <a href="{{route('cancel.create', ['tender', $tender->id])}}" id="tender-cancel" class="btn btn-danger">{{Lang::get('keys.cancel_tender')}}</a>
                @elseif ($tender->canCancel() && $tender->cancel)
                    <a href="{{route('cancellation.edit', [$tender->cancel->id])}}" id="tender-cancel" class="btn btn-danger">{{Lang::get('keys.cancel_tender')}}</a>
                @endif
            @elseif (Auth::check())
                @if ((($tender->canComplaint())  && Auth::user()->organization->type == 'supplier') || $tender->canComplaintQualification())
                    <a  class="btn btn-danger" href="{{route('claim.create', ['tender', $tender->id])}}">{{Lang::get('keys.create_complaint')}}</a>
                @endif
                @if ($tender->winner && $tender->procedureType->threshold_type == 'above.limited' && Auth::user()->organization->type == 'supplier' && time() < strtotime($tender->winner->complaint_date_end))
                    <a  class="btn btn-danger skarga" href="{{route('claim.create', ['award', $tender->winner->id])}}">{{Lang::get('keys.create_claim_winner_')}}</a>
                @endif

            @endif
        @elseif($tender->canTender() || $tender->canQuestion())
            @if (!$tender->multilot)
                <a href="{{route('bid.new', ['tender', $tender->id])}}" class="btn btn-success">{{Lang::get('keys.create_bid')}}</a>
            @endif
        @endif
    </div>
</div>

@if (Auth::check() && $tender->isOwner(Auth::user()->id) && Auth::user()->organization->mode == 1 && $tender->mode == 0)
    @include('share.component.modal-confirm', ['modalNamespace' => 'publish' . $tender->id, 'modalTitle' => 'Публікація', 'modalMessage' => 'Зверніть увагу, що закупівля буде опублікована в тестовому режимі.'])
@endif

