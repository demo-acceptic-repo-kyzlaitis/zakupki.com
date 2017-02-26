<?php $route =  Route::getCurrentRoute()->getName(); ?>
<ul class="nav nav-tabs" role="tablist">
    <li class="@if ($route == 'tender.show' || $route == 'tender.showByID') active @endif"><a href="{{route('tender.show', [$tender->id])}}">Закупівля</a></li>
    @if ($tender->questions->count() > 0)
        <li class="@if ($route == 'questions.index') active @endif"><a href="{{route('questions.index', ['tender', $tender->id])}}">Запитання ({{$tender->questions->count()}}) </a></li>
    @endif
    <?php
        $hasAwardClaims = false;
        if ($tender->awards()->count()) {
            foreach ($tender->awards as $award) {
                if ($award->complaint) {
                    $hasAwardClaims = true;
                }
            }
        }

    ?>
    @if ($tender->procedureType->procurement_method == 'selective')
        <li class="@if ($route == 'tender.participants') active @endif"><a href="{{route('tender.participants', [$tender->id])}}">Допущені учасники </a></li>
    @endif
    @if (($tender->type_id == 4  || $tender->type_id == 5 || $tender->type_id == 6) && $tender->award)
        <li class="@if ($route == 'award.create' || $route == 'award.edit') active @endif"><a href="{{route('award.show', [$tender->award->id])}}">Переможець</a></li>
    @endif
    @if ($tender->allComplaints->count() > 0 || $hasAwardClaims || $tender->hasNotAnsweredComplaints())
        <li class="@if (strpos($route, 'claim') === 0 || strpos($route, 'complaint') === 0) active @endif"><a href="{{route('claim.index', ['tender', $tender->id])}}"> @if ($tender->procedureType->procurement_method == 'limited') Скарги @else Вимоги/Скарги @endif ({{$tender->allComplaints->count()}})</a></li>
    @endif
    @if ($tender->cancel && ($tender->canCancelAfterComplaintEndDate() || !$tender->hasPendingComplaints()))
        @if ($tender->cancel->status == 'active' )
            <li class="@if ($route == 'cancellation.show') active @endif"><a href="{{route('cancellation.show', [$tender->cancel->id])}}">Заявка на відміну закупівлі</a></li>
        @elseif ($tender->isOwner())
            <li class="@if ($route == 'cancellation.edit') active @endif"><a href="{{route('cancellation.edit', [$tender->cancel->id])}}">Заявка на відміну закупівлі</a></li>
        @endif
    @endif
    @if ($tender->allBids()->count() > 0 && !in_array($tender->status, ['active.enquiries', 'active.tendering', 'active.auction', 'cancelled', 'unsuccessful']))
        <li class="@if ($route == 'tender.bids') active @endif"><a href="{{route('tender.bids', [$tender->id])}}">Пропозиції </a></li>
    @endif
    @if ($tender->contracts()->count())
        <li class="@if ($route == 'tender.contracts' || $route == 'contract.show') active @endif"><a href="{{route('tender.contracts', [$tender->id])}}">Контракти ({{$tender->contracts->count()}})</a></li>
    @endif
</ul>
<hr>