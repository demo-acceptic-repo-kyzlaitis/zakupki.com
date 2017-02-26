<table  class="table table-striped table-bordered">
    <tr>
        <th>ID</th>
        <th>Номер</th>
        <th>Назва</th>
        @if (Auth::check() && Auth::user()->super_user)
            <th>Source</th>
        @endif
        <th>Дата початку</th>
        <th>Процедура</th>
        <th>Бюджет</th>
        <th></th>
    </tr>
        @foreach($plans as $plan)
            <tr>
                <td>
                    {{$plan->id}}
                </td>
                <td><a href="https://prozorro.gov.ua/plan/{{$plan->planID}}/">{{$plan->planID}}</a></td>
                <td><a href="{{route('plan.show', [$plan->id])}}">{{$plan->description}}</a></td>
                @if (Auth::check() && Auth::user()->super_user)
                    <td><a href="{{env('PRZ_API')}}/plans/{{!empty($plan->cbd_id) ? $plan->cbd_id : null}}">source</a></td>
                @endif
                <td>@if($plan->start_month && $plan->start_year)
                        @if($plan->start_day){{$plan->start_day}}.@endif{{$plan->start_month}}.{{$plan->start_year}}
                    @else
                        {{$plan->start_date}}
                    @endif</td>
                <td>{{$plan->procedure->procedure_name}}</td>
                <td>{{$plan->amount}} {{$plan->currency->currency_code}}</td>
                <td><a href="{{route('plan.edit', [$plan->id])}}" class="btn btn-xs btn-info helper" title-data="Редагування"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></a></td>
            </tr>
        @endforeach
</table>
@if(count($plans) == 0)
<h3 class="text-center">За вашим запитом не знайдено жодного плану закупівлі</h3>
@endif