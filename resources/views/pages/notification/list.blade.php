@extends('layouts.index')

@section('content')
    <div class="container">
        <div class="row">
    @if (Auth::user()->subscribe == 0)
            <p class="well">Ви не підписані на розсилку. Для того щоб підписатись перейдіть за  <a href="{{route('notification.subscribe')}}"> посиланням.</a></p>
            @else
                <p class="well">Для того щоб відписатись перейдіть за  <a href="{{route('notification.unsubscribe')}}"> посиланням.</a></p>

    @endif



        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Об`єкт</th>
                    <th>Подія</th>
                    <th>Дата</th>

                </tr>
            </thead>
            <tbody>

                @foreach($notifications as $notification)

                    <tr @if (is_null($notification->readed_at)) class="active" @endif>
                        <td width="200" @if (is_null($notification->readed_at)) style="font-weight: bold" @endif ><a href="{{route('notification.show', [$notification->id])}}">{{$notification->title}}</a></td>
                        <td width="200">{{$notification->getEvent()}}</td>
                        <td width="200">{{$notification->created_at}}</td>

                    </tr>
                @endforeach

            </tbody>
        </table>
        {!! $notifications->render() !!}
    </div>
</div>
@endsection