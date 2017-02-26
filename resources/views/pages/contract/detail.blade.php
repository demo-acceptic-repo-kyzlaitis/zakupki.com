@extends('layouts.index')

@section('content')
    {{--Editing Section Start--}}

    <div class="container">
        <div class="row">
            <div class="col-md-12">
                @include('share.component.title')
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                @include('share.component.buttons')
                @include('share.component.tabs')

            </div>
        </div>
        @if ($contract->tender->procedureType->threshold_type != 'below' && $contract->tender->procedureType->threshold_type != 'below.limited')
            @include('share.component.signature', ['entity' => $contract])
        @endif
        <div style="">
            @if ($contract->status != 'terminated' && $contract->tender->isOwner())
                @if ($contract->access_token != '')
                    @if ($contract->tender->procedureType->procurement_method_type == 'reporting')
                        <a href="{{route('contract.terminate', [$contract->id, 'type' => 'fail'])}}" class="btn btn-danger">{{Lang::get('keys.contract_fail')}}</a>
                        <a href="{{route('contract.terminate', [$contract->id, 'type' => 'success'])}}" class="btn btn-success">{{Lang::get('keys.contract_success')}}</a>
                    @else
                        <a href="{{route('contract.terminate', [$contract->id, 'type' => 'success'])}}" class="btn btn-success">{{Lang::get('keys.contract_success')}}</a>
                        <a href="{{route('contract.terminate', [$contract->id, 'type' => 'fail'])}}" class="btn btn-danger">{{Lang::get('keys.contract_fail')}}</a>
                    @endif

                        @if (!$contract->change)
                        <a href="{{route('contract.change', [$contract->id])}}" class="btn btn-warning">{{Lang::get('keys.contract_publish_change')}}</a>
                        @else
                            <a href="{{route('contract.change', [$contract->id])}}" class="btn btn-warning">{{Lang::get('keys.contract_edit_change')}}</a>
                        @endif
                @endif
            @endif
        </div>

        <?php
        $organization = $contract->award->bid ? $contract->award->bid->organization : $contract->award->organization;
        ?>
        <h4>{{$organization->name}}</h4>
        <table class="clean-table">
            <tr>
                <th>Статус:</th>
                <td><span class="label label-{{$contract->statusDesc->style}}">{{$contract->statusDesc->description}}</span></td>
            </tr>
            <tr>
                <th>ID:</th>
                <td>{{$contract->contractID}}</td>
            </tr>
            @if ($contract->amount_paid > 0)
                <tr>
                    <th>Фактично оплачена сума:</th>
                    <td>{{$contract->amount_paid}} {{$contract->award->currency->currency_description}} </td>
                </tr>
            @endif
            @if ($contract->termination_details != '')
                <tr>
                    <th>Причина розірвання:</th>
                    <td>{{$contract->termination_details}} </td>
                </tr>
            @endif
            @if (!empty($contract->contract_number))
                <tr>
                    <th>Номер договору:</th>
                    <td>{{$contract->contract_number}}</td>
                </tr>
            @endif
            @if (!empty($contract->period_date_start))
                <tr>
                    <th>Початок дії договору:</th>

                    <td>{{$contract->period_date_start}}</td>

                </tr>
            @endif
            @if (!empty($contract->period_date_end))

                <tr>
                    <th>Закінчення дії договору:</th>
                    <td>{{$contract->period_date_end}}</td>
                </tr>
            @endif

            @if (!empty($contract->date_signed))
                <tr>
                    <th>Дата підписання:</th>
                    <td>{{$contract->date_signed}}</td>
                </tr>
            @endif
            <tr>
                <th>Код ЄДРПОУ:</th>
                <td>{{$organization->identifier}}</td>
            </tr>
            <tr>
                <th>Контактна особа:</th>
                <td>{{$organization->contact_name}}</td>
            </tr>
            <tr>
                <th>Поштова адреса:</th>
                <td>{{$organization->getAddress()}}</td>
            </tr>
            <tr>
                <th>Сума:</th>

                <td>
                    <p class="text-success"><span class="current_amount">{{$contract->amount ? $contract->amount : $contract->award->amount}}</span><input size="10" type="hidden" class="amount" value="{{$contract->amount ? $contract->amount : $contract->award->amount}}"> {{$contract->award->currency->currency_description}} @if ($contract->award->tax_included) (Враховуючи ПДВ) @else {{ '(Без ПДВ)' }} @endif

                    <input type="hidden" class="max" value="{{$contract->amount ? $contract->amount : $contract->award->amount}}">
                    <?php if($tender->type_id == 8){ ?>
                        <a href="javascript:" class="btn btn-xs btn-info helper" title-data="Редагування"><span  class="glyphicon glyphicon-pencil amount-edit"aria-hidden="true"></span></a>
                   <?php }?>
                    </p>
                </td>
            </tr>
            @if ($contract->documents()->count() && ($contract->status == 'active' || $contract->status == 'terminated'))
                <tr>
                    <th>Документи контракту</th>
                    <td>@include('share.component.document-list', ['entity' => $contract, 'size' => 'file-icon-sm'])</td>
                </tr>
            @endif


        </table>

        @if ($contract->tender->canContract())

            @if ($contract->canEdit(Auth::user()->organization->id))
                <h4>Договір</h4>
                @include('pages.award.component.contract-form')
            @else

            @endif
        @else

            <h4>Договір</h4>
            <div class="container"><div class="alert alert-danger" role="alert">
                    Підписання контракту можливе після {{ date('d.m.Y H:i', getDateWithWorkdays(strtotime($contract->tender->award_start_date), 2)) }}
                </div></div>
        @endif


        @if ($contract->changes)
            @foreach($contract->changes as $change)
                @if ($change->status == 'active')
                    <hr>
                    <h3>Зміни до договору від {{$change->date}}</h3>
                    <table class="clean-table">
                        <tr>
                            <th>Номер договору:</th>
                            <td>{{$change->contract_number}}</td>
                        </tr>
                        <tr>
                            <th>Дата підписання:</th>
                            <td>{{$change->date_signed}}</td>
                        </tr>

                        <tr>
                            <th>Тип зміни:</th>
                            <td>{{$change->rationaleType->title}}</td>
                        </tr>

                        <tr>
                            <th>Причина зміни:</th>
                            <td>{{$change->rationale}}</td>
                        </tr>

                    </table>
                @endif
            @endforeach
        @endif
    </div>
@endsection