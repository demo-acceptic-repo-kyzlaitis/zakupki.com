@extends('layouts.index')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h2>Закупівлі які були знайдені минулого разу</h2>
            </div>
            <div class="col-md-6 ">
                <h2><a href="{{route('agent.show', ['id' => $agent->id])}}" class="btn btn-info btn-sm pull-right">{{Lang::get('keys.to_new_tenders')}}</a></h2>
            </div>

        </div>
        <div style="margin-bottom: 50px"></div>
        <table class="table table-striped">

            @foreach($tendersFoundToday as $foundTender)

                <tr>
                    <td width="50%" style="padding: 20px 10px">
                        {{--<div style="padding: 5px 0"><b><a href="{{route('tender.showByID', ['TenderID' => $tender->tenderID])}}">{{$tender->title}}</a></b></div>--}}
                        <div style="padding: 5px 0"><b><a href="{{route('tender.show', ['id' => $foundTender->tender->id])}}">{{$foundTender->tender->title}}</a></b></div>
                        <div style="padding: 5px 0; font-size: 14px; font-weight: bold">{{$foundTender->tender->procedureTypeDesc()}}</div>
                        <div style="padding: 5px 0; font-size: 14px">{{$foundTender->tender->description}}</div>

                        <div style="color: gray; padding: 5px 0; font-size: 14px"> @if(!empty($foundTender->tender->organization)) {{$foundTender->tender->organization->name}} @endif</div>
                        <div style="padding: 5px 0; color: gray; font-size: 13px">
                            @if ($foundTender->tender->type_id == 1)
                                Період уточнень до <span
                                        style="background-color: #eeefef; color: #343434; margin-right: 5px; padding: 6px 5px; zoom: 1;">{{$foundTender->tender->enquiry_end_date}}</span>
                            @endif
                            Прийом пропозицій до <span style="background-color: #eeefef; color: #343434; margin-right: 5px; padding: 6px 5px; zoom: 1;">{{$foundTender->tender->tender_end_date}}</span>
                        </div>
                    </td>
                    <td style="padding: 20px 10px">
                        <div style="font-size: 20px; font-weight: bold;">{{number_format($foundTender->tender->amount, 2, '.', ' ')}} <span style="font-size: 12px; color: gray;">@if(!empty($foundTender->tender->currency)){{$foundTender->tender->currency->currency_description}}@endif</span></div>
                    </td>
                    <td style="padding: 20px 10px">
                        <div style="margin-bottom: 10px">№ {{$foundTender->tender->tenderID}}</div>
                        @if (!empty($foundTender->tender->statusDesc))
                            <span class="label label-@if(!empty($foundTender->tender->statusDesc)){{$foundTender->tender->statusDesc->style}} @endif">
                                        @if(!empty($foundTender->tender->statusDesc))
                                    {{$foundTender->tender->statusDesc->description}}
                                @endif</span>
                        @else
                            <span class="label label-default">{{$foundTender->tender->status}}</span>
                        @endif

                    </td>

                </tr>
            @endforeach
        </table>
        <div class="text-center">
            {!! $tendersFoundToday->render() !!}
        </div>




    </div>
@endsection