@extends('layouts.index')
{{--{{Session::put('organizationIds',[])}}--}}
@section('content')
    <div class="container">
        <div class="row">
            <section class="col-md-12 add-organization">
                <a href="{{route('organization.edit')}}" class="pull-right">Редагувати дані організації</a>
            </section>
        </div>
        @if($organizations->count())
            <div class="row">
                @foreach($organizations as $organization)
                    <div class="col-md-12 about-organization">

                        <div class="row">
                            <div class="col-md-3">Тип організації</div>
                            <div class="col-md-8"></div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">Назва організації</div>
                            <div class="col-md-8">{{$organization->name}}</div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">Код ЄДРПОУ</div>
                            <div class="col-md-8">{{$organization->identifier}}</div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">Країна</div>
                            <div class="col-md-8">{{$organization->country_id}}</div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">Регіон</div>
                            <div class="col-md-8">{{$organization->region_id}}</div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">Поштовий індекс</div>
                            <div class="col-md-8">{{$organization->postal_code}}</div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">Баланс</div>
                            <div class="col-md-8">{{$organization->user->getBalance()}}  {{$organization->user->balance->currency}}</div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">Населений пункт</div>
                            <div class="col-md-8">{{$organization->locality}}</div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">Поштова адреса</div>
                            <div class="col-md-8">{{$organization->street_addres}}</div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">Контактна особа</div>
                            <div class="col-md-8">{{$organization->contact_name}}</div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">Телефон</div>
                            <div class="col-md-8">{{$organization->contact_phone}}</div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">Email</div>
                            <div class="col-md-8">{{$organization->contact_email}}</div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">Сайт</div>
                            <div class="col-md-8">{{$organization->contact_url}}</div>
                        </div>

                    </div>
                @endforeach
            </div>

        @else
            <h2>List empty</h2>
        @endif
    </div>
@endsection


