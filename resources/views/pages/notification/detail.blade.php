@extends('layouts.index')
@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h2>{{$notification->title}}</h2>
                <p class="well"><?php echo $notification->text; ?></p>
                <br><br>
                <a href="{{route('notification.index')}}"> &larr; {{Lang::get('keys.back')}}</a>
            </div>
        </div>
    </div>
@endsection