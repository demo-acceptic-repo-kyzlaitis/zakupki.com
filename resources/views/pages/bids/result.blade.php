@extends('layouts.index')

@section('content')
    <div class="container">
        @if(!empty($id)) <h2>ID: {{$id}}</h2> @endif
        @if(!empty($httpErrors))
            <div class="alert alert-danger" role="alert">
                <?php
                array_map(function ($x) {
                    (new \Illuminate\Support\Debug\Dumper)->dump($x);
                }, $httpErrors);
                ?>
            </div>
        @endif
    </div>
@endsection