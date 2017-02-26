@extends('layouts.admin')


@section('content')
    <div class="container">
        <div class="row">
            <h2>Анкети користувачів</h2>
        </div>
    </div>

    @if(empty($agents) && $agents->count() === 0)
        Пошукових агенті не існує
    @else
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>id</th>
                    <th>О/р</th>
                    <th>Назва організації</th>
                    <th>ЄДРПОУ</th>
                    <th>Дії</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach($agents as $agent)
                    <tr>
                        <td>{{$agent->id}}</td>
                        <td>{{$agent->organization->id}}</td>
                        <td>{{$agent->organization->name}}</td>
                        <td>{{$agent->organization->identifier}}</td>
                        <td>
                            <a href="{{route('admin::agent.edit', $agent->id)}}"
                               class="btn btn-xs btn-warning helper" title-data="{{Lang::get('keys.cancel')}}">
                                <span class="glyphicon glyphicon-edit" aria-hidden="true"></span>
                            </a>
                        </td>
                        {{--<td>{{$agent->dk2015->name}}</td> TODO закончить коды--}}
                        {{--<td>{{$agent->dk2010->name}}</td>--}}
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection