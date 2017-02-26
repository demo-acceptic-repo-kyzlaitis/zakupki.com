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
        @if ($tender->multilot)
            @foreach($tender->lots as $index => $lot)
                <div style="background: #eee; padding: 10px; margin-bottom: 15px; line-height: 2em;"><b>{{$index + 1}}. {{$lot->title}}</b></div>
                <?php $bids = $lot->bids()->ordered()->get(); ?>
                @foreach($bids as $i => $bid)
                    @if ($bid->award && ($bid->award->status == 'active' || $bid->award->status == 'pending' ))
                        <div class="row"> <div class="col-md-12"> <div class="col-md-12"> @include('pages.bids.component.bid-detail')</div></div></div>
                        <?php $bids->forget($i);?>
                    @endif
                @endforeach
                @foreach($bids as $bid)
                    <div class="row"> <div class="col-md-12"> <div class="col-md-12"> @include('pages.bids.component.bid-detail')</div></div></div>
                @endforeach
            @endforeach
        @else
            <?php $bids = $tender->bids; ?>
            @foreach($bids as $i => $bid)
                @if ($bid->award && ($bid->award->status == 'active' || $bid->award->status == 'pending'))
                    <div class="row"> <div class="col-md-12"> <div class="col-md-12"> @include('pages.bids.component.bid-detail')</div></div></div>
                    <?php $bids->forget($i);?>
                @endif
            @endforeach
            @foreach($bids as $bid)
                <div class="row"> <div class="col-md-12"> <div class="col-md-12"> @include('pages.bids.component.bid-detail')</div></div></div>
            @endforeach
        @endif
    </div>
@endsection