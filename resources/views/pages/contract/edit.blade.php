@extends('layouts.index')

@section('content')
    {{--Editing Section Start--}}

    <section class="registration container">
        <div class="text-right">
            @if ($tender->published_at == '0000-00-00 00:00:00')
                <a href="{{route('tender.publish', [$tender->id])}}" class="btn btn-info">{{Lang::get('keys.publish')}}</a>
            @endif
            @if (strtotime($tender->tender_start_date) > time() && ($tender->status == 'active.enquiries' || $tender->status == 'draft'))
                <a href="{{route('tender.edit', [$tender->id])}}" class="btn btn-info">{{Lang::get('keys.edit')}}</a>
            @endif
            @if ($tender->cbd_id != '' && $tender->status != 'cancelled' && $tender->status != 'complete')
                <a href="{{route('tender.cancel', [$tender->id])}}" class="btn btn-danger">{{Lang::get('keys.cancel')}}</a>
            @endif
        </div>
        <h2>Тендер {{$tender->tenderID}}

            <?php
            $labels = [
                    'draft' => 'default',
                    'active.enquiries' => 'info',
                    'cancelled' => 'default',
                    'active.qualification' => 'warning',
                    'active.tendering' => 'primary',
                    'unsuccessful' => 'danger',
                    'active.awarded' => 'success',
                    'active.auction' => 'warning',
                    'complete' => 'success'
            ];
            ?>
            <span class="label label-{{$labels[$tender->status]}}">{{$tender->statusDesc()->first()->description}}</span>
        </h2>
        <div>&nbsp;</div>
        @include('share.component.tabs')
        @if($errors->has())
            <div class="alert alert-danger" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        {!! Form::model($contract,['route' => ['contract.update', $contract->id],
        'method'=>'PUT',
        'enctype'=>'multipart/form-data',
        'class'=>'form-horizontal',]) !!}
        <fieldset>


            @include('pages.contract.component.form')


            <hr>
            <div class="form-group">
                <div class="col-lg-12 text-center">
                    {!! Form::submit(Lang::get('keys.save'),['class'=>'btn btn-lg btn-danger']) !!}
                    @if($contract->documents->count() > 0)
                    <a href="{{route('contract.activate', [$contract->id])}}" class="btn btn-lg btn-info">{{Lang::get('keys.sign')}}</a>
                        @endif
                </div>
            </div>
        </fieldset>
        {!! Form::close() !!}
    </section>
    {{--Editing Section End--}}
@endsection