@extends('layouts.index')


@section('content')
    <div class="container">
        <div class="row">
            <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
                <h2 style="margin-top: 0px;margin-bottom: 0px;">Пошукові агенти</h2>
            </div>
            <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3 pull-right">
                <div class="form-group">
                    <div class="col-sm-10">
                        <a class="btn btn-danger" data-toggle="modal" href="{{route('agent.create')}}">{{Lang::get('keys.create_search_agent')}}</a>
                    </div>
                </div>
            </div>
        </div>
        <hr>

        @if(empty($agents) && $agents->count() === 0 )
            <h3>У вас не має створенних агентів</h3>
        @else
                <div class="row">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>id</th>
                                <th>Назва пошукового агента</th>
                                <th>Частота розсилки</th>
                                <th>Кількість актуальних закупівель</th>
                                <th>Статус</th>
                                <th>Редагування </th>
                            </tr>
                            </thead>
                            <tbody>
                                @foreach($agents as $agent)
                                    <tr class="@if($agent->status == 'active') success @else warning @endif">
                                        <td>{{$agent->id}}</td>
                                        <td>{{$agent->field}}</td>
                                        <td>@if($agent->email_frequency  == 'weekly') Щонеделі @elseif($agent->email_frequency  == 'daily') Щоденно @else Не визначено @endif</td>
                                        <th><a href="{{route('agent.show', $agent->id)}}">{{$agent->agentHistory()->with('tender')->whereDate('created_at', '=', Carbon\Carbon::today()->toDateString())->count()}}</a></th>
                                        <td>@if($agent->status == 'pending') На модерації @elseif($agent->status == 'suspended') Призупенений через редагування @elseif($agent->status == 'active') Активний @else Призупинений @endif</td>
                                        <td>
                                            <a href="{{route('agent.edit', $agent->id)}}" class="btn btn-xs btn-info helper" title-data="Редагування">
                                                <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                            </a>

                                            {{--<a href="{{route('agent.destroy', $agent->id)}}" data-href="" class=" btn btn-danger btn-xs" title-data="Видалення">--}}
                                                {{--<span class="glyphicon glyphicon-trash" aria-hidden="true"></span>--}}
                                            {{--</a>--}}

                                        </td>

                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
        @endif


    </div>


@endsection