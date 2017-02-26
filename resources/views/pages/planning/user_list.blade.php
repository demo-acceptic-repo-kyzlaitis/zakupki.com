@extends('layouts.index')

@section('content')
    <div class="container">
        <h2>Мої додатки до плану</h2><hr>
        @if($plans->count())
            <div class="row">
                @if(!empty($filters))
                    {!! $filters !!}
                @endif
                <div id="{{$listName}}">
                    @include('pages.planning._part.list', ['plans' => $plans])
                </div>
            </div>
            <div class="text-center">
                {!! $plans->render() !!}
            </div>
        @else
            <div class="well text-center">
                <h2>У Вас немає створених додатків до плану</h2>
                <a href="{{route('plan.create')}}" class="btn btn-warning">{{Lang::get('keys.create_plan')}}</a>
            </div>
        @endif
    </div>
@endsection



