@extends('layouts.index')

@section('content')

    <div class="container">
        @if($errors->has() && Input::old('error_qualify') == 'y')
            <div class="alert alert-danger" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
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
        @if ($firstStage->multilot)
            @foreach($firstStage->lots as $lot)
                <div style="background: #eee; padding: 10px; margin-bottom: 15px; line-height: 2em;"><b>1. {{$lot->title}}</b></div>
                @if ($lot->bids->count())
                    @foreach($lot->bids as $bid)
                        <div class="row">
                            <div class="col-md-12">
                                <div class="col-md-12">
                                    <table class="clean-table">
                                        <tr>
                                            <th>Назва організації:</th>
                                            <td>{{$bid->organization->name}}</td>
                                        </tr>
                                        <tr>
                                            <th>Код ЄДРПОУ:</th>
                                            <td>{{$bid->organization->identifier}}</td>
                                        </tr>
                                        <tr>
                                            <th>Контактна особа:</th>
                                            <td>{{$bid->organization->contact_name}}</td>
                                        </tr>
                                        <tr>
                                            <th>Email:</th>
                                            <td>{{$bid->organization->contact_email}}</td>
                                        </tr>
                                        <tr>
                                            <th>Телефон:</th>
                                            <td>{{$bid->organization->contact_phone}}</td>
                                        </tr>
                                        <tr>
                                            <th>Поштова адреса:</th>
                                            <td>{{$bid->organization->getAddress()}}</td>
                                        </tr>
                                    </table>
                                    <hr>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="row">
                        <div class="col-md-12">
                            Жодна організація не допущена до другого етапу.
                        </div>
                    </div>
                @endif
            @endforeach
        @else
            @if ($firstStage->bids->count())
                @foreach($firstStage->bids as $bid)
                    <div class="row">
                        <div class="col-md-12">
                            <div class="col-md-12">
                                <table class="clean-table">
                                    <tr>
                                        <th>Назва організації:</th>
                                        <td>{{$bid->organization->name}}</td>
                                    </tr>
                                    <tr>
                                        <th>Код ЄДРПОУ:</th>
                                        <td>{{$bid->organization->identifier}}</td>
                                    </tr>
                                    <tr>
                                        <th>Контактна особа:</th>
                                        <td>{{$bid->organization->contact_name}}</td>
                                    </tr>
                                    <tr>
                                        <th>Email:</th>
                                        <td>{{$bid->organization->contact_email}}</td>
                                    </tr>
                                    <tr>
                                        <th>Телефон:</th>
                                        <td>{{$bid->organization->contact_phone}}</td>
                                    </tr>
                                    <tr>
                                        <th>Поштова адреса:</th>
                                        <td>{{$bid->organization->getAddress()}}</td>
                                    </tr>
                                </table>
                                <hr>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="row">
                    <div class="col-md-12">
                        Жодна організація не допущена до другого етапу.
                    </div>
                </div>
            @endif
        @endif
    </div>
@endsection