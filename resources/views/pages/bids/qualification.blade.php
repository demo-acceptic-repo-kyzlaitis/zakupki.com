@extends('layouts.index')

@section('content')

    <div class="container">
        @if($errors->has())
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
        <div class="row">
            <div class="col-md-12">
                <div class="col-md-12"> @include('pages.bids.component.bid-detail')</div>
            </div>
        </div>

        @if($tender->isOwner() && $tender->status == 'active.pre-qualification')
            <h3 class="text-center">Кваліфікувати пропозицію</h3>

            {!! Form::open(['url' => route('bid.qualify', [$qualification->id]),
                    'method'=>'POST',
                    'enctype'=>'multipart/form-data',
                    'class'=>'form-horizontal',]) !!}

            @include('pages.bids.component.form_qualify', ['entity' => $qualification])

            {!! Form::close() !!}

            <script>
                var grounds = $.parseJSON('<?php echo json_encode($groundsForRejections['descriptions']);?>'),
                        groundsForRejection = [];
                $.each(grounds, function (key, val) {
                    groundsForRejection[key] = val;
                });
            </script>
        @endif

    </div>
@endsection