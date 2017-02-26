@extends('layouts.index')
{{--{{Session::put('documentIds',[])}}--}}
@section('content')
    <div class="container">
        <div class="row">
            <section class="col-md-12 add-tender">
                <div class="pull-right navigation-bar">
                    <a href="{!! route('organization.tender.complaint.create',[$organizationWrongId,$tenderWrongId]) !!}">
                        {{Lang::get('keys.create_complaint_tender')}}
                    </a>
                    <a href="{!! route('organization.tender.question.create',[$organizationWrongId,$tenderWrongId]) !!}">
                        {{Lang::get('keys.create_question')}}
                    </a>
                    <a href="{!! route('organization.tender.document.create',[$organizationWrongId,$tenderWrongId]) !!}">
                        {{Lang::get('keys.add_documentation')}}
                    </a>
                    <a href="{!! route('organization.edit',[$organizationWrongId]) !!}">
                        {{Lang::get('keys.go_to_organization')}}
                    </a>
                    <a href="{{route('organization.tender.edit',[$organizationWrongId,$tenderWrongId])}}">
                        {{Lang::get('keys.go_to_tender')}}
                    </a>
                </div>
            </section>
        </div>
        @if($documents->count())
            <div class="row">
                @foreach($documents as $documentWrongId => $document)
{{--                    {{Session::push('documentIds',$document->id)}}--}}
                    <div class="col-md-12">
                        <div class="panel panel-primary">
                            <div class="panel-heading">
                                <h3 class="panel-title">{{$document->title}}</h3>
                            </div>
                            <div class="panel-body">
                                <ul class="list-unstyled">
                                    <li>
                                        <strong>Ідентифікатор документу:</strong>
                                        {{$document->id}}
                                    </li>
                                    <li>
                                        <strong>Назва документу:</strong>
                                        {{$document->title}}
                                    </li>
                                    <li>
                                        <strong>Опис документу:</strong> <br/>
                                        {{$document->description}}
                                    </li>
                                    <li>
                                        <strong>Посилання на документ:</strong> <br/>
                                        {{$document->url}}
                                    </li>
                                    <li>
                                        <strong>Формат документу:</strong> <br/>
                                        {{$document->format}}
                                    </li>
                                    <li>
                                        <h5>Дата створення:</h5>
                                    </li>
                                    <li>
                                        {{$document->created_at}}
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

        @else
            <h2>List empty</h2>
        @endif
    </div>
@endsection


