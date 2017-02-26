<h4>{{$contract->award->bid->organization->name}}</h4>
<table class="clean-table">
    <tr>
        <th>Статус:</th>
        <td><span class="label label-{{$contract->statusDesc->style}}">{{$contract->statusDesc->description}}</span></td>
    </tr>
    <tr>
        <th>ID:</th>
        <td>{{$contract->contractID}}</td>
    </tr>
    @if (!empty($contract->contract_number))
    <tr>
        <th>Номер договору:</th>
        <td>{{$contract->contract_number}}</td>
    </tr>
    @endif

        <tr>
            <th>Початок дії договору:</th>
            @if (!empty($contract->period_date_start))
            <td>{{$contract->period_date_start}}</td>
            @endif
        </tr>


            <tr>
                <th>Закінчення дії договору:</th>
                @if (!empty($contract->period_date_end))
                <td>{{$contract->period_date_end}}</td>
                @endif
            </tr>

    @if (!empty($contract->date_signed))
        <tr>
            <th>Дата підписання:</th>
            <td>{{$contract->date_signed}}</td>
        </tr>
    @endif
    <tr>
        <th>Код ЄДРПОУ:</th>
        <td>{{$contract->award->bid->organization->identifier}}</td>
    </tr>
    <tr>
        <th>Контактна особа:</th>
        <td>{{$contract->award->bid->organization->contact_name}}</td>
    </tr>
    <tr>
        <th>Поштова адреса:</th>
        <td>{{$contract->award->bid->organization->getAddress()}}</td>
    </tr>
    <tr>
        <th>Сума:</th>
        <td><p class="text-success">{{$contract->amount ? $contract->award->amount : $contract->amount}} {{$contract->award->currency->currency_description}} @if ($contract->award->tax_included) (Враховуючи ПДВ) @endif</p></td>
    </tr>
    @if ($contract->documents()->count() && $contract->status == 'active')
        <tr>
            <th>Документи контракту</th>
            <td>@include('share.component.document-list', ['entity' => $contract, 'size' => 'file-icon-sm'])</td>
        </tr>
    @endif


</table>

<hr>
@if ($contract->tender->canContract())
    @if ($contract->canEdit(Auth::user()->organization->id))
        <h4>Договір</h4>
        @include('pages.award.component.contract-form')
    @else

    @endif
@else
    <h4>Договір</h4>
    <div class="container"><div class="alert alert-danger" role="alert">
            Підписання контракту можливе після {{date('d.m.Y H:i', strtotime($contract->tender->award_start_date) + 2 * 24 * 3600)}}
        </div></div>
@endif
