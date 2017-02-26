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
                @include('share.component.tabs')
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                @if (in_array($complaint->status, ['draft', 'claim', 'answered', 'satisfied']) && $complaint->satisfied === null)
                    <section class="registration container">
                        @if($errors->has())
                            <div class="alert alert-danger" role="alert">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        {!! Form::model($complaint,['route' => ['complaint.update', $complaint->id],
                        'method'=>'PUT',
                        'enctype'=>'multipart/form-data',
                        'class'=>'form-horizontal',]) !!}
                        <fieldset>
                            @if (!$complaint || $complaint->status == 'draft')
                                @include('pages.complaint.component.draft')
                            @elseif($complaint->status == 'claim')
                                @if ($complaint->tender_organization_id == Auth::user()->organization->id)
                                    @include('pages.complaint.component.claim')
                                @else
                                    @include('pages.complaint.component.complaint-detail')
                                @endif
                            @elseif ($complaint->status == 'answered')
                                @if ($complaint->organization_id == Auth::user()->organization->id)
                                    @include('pages.complaint.component.answered')
                                @else
                                    @include('pages.complaint.component.complaint-detail')
                                @endif
                            @elseif ($complaint->status == 'satisfied')
                                @if ($complaint->organization_id == Auth::user()->organization->id)
                                     @include('pages.complaint.component.complaint-detail')
                                @else

                                    @include('pages.complaint.component.satisfied')
                                @endif
                            @endif
                        </fieldset>
                        {!! Form::close() !!}
                    </section>
                @else
                    @include('pages.complaint.component.complaint-detail')
                @endif
            </div>
        </div>
        {{--Editing Section End--}}
    </div>
@endsection