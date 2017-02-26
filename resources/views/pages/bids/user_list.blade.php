@extends('layouts.index')

@section('content')
    <div class="container">
        <h2>Мої пропозиції</h2><hr>
        @if($bids->count())
            <div class="row">
                {!! $filters !!}
                <div id="{{$listName}}">
                    @include('pages.bids._part.list', ['bids' => $bids])
                </div>
            </div>
            <div class="text-center">
                {!! $bids->render() !!}
            </div>

        @else
            <div class="well text-center">
                <h2>У Вас немає поданих заявок</h2>
                <a href="{{route('tender.list')}}" class="btn btn-warning">{{Lang::get('keys.get_active_tenders')}}</a>
            </div>
        @endif
    </div>
@endsection



