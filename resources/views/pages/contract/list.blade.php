@extends('layouts.index')

@section('content')
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
        <table class="table table-hover">
            <thead>
            <tr>
                <th>№</th>
                <th>№ Контракту</th>
                <th>Лот</th>
                <th>Статус</th>
            </tr>
            </thead>
            <tbody>
            @foreach($tender->contracts as $contractIndex => $contract)
                <tr>
                <td>{{$contractIndex + 1}}</td>
                <td><a href="{{route('contract.show', [$contract->id])}}">{{$contract->contractID}}</a></td>
                <td><a href="{{route('tender.show', [$contract->tender->id])}}">@if ($contract->award->bid) {{$contract->award->bid->bidable->title}} @else {{$contract->tender->title}} @endif</a></td>
                <td><span class="label label-{{$contract->statusDesc->style}}">{{$contract->statusDesc->description}}</span></td>
                </tr>
            @endforeach

            </tbody>
        </table>
    </div>
@endsection