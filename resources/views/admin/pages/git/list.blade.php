@extends('layouts.admin')

@section('content')
    @if(session('status'))
        <div class="alert alert-success">
            <strong>Success!</strong>
        </div>
    @endif
    <style>
        .rainbow {
            background: linear-gradient(312deg, #0049ff, #f9ff00);
            background-size: 400% 400%;

            -webkit-animation: AnimationName 16s ease infinite;
            -moz-animation: AnimationName 16s ease infinite;
            animation: AnimationName 16s ease infinite;
        }
        @-webkit-keyframes AnimationName {
            0%{background-position:0% 5%}
            50%{background-position:100% 96%}
            100%{background-position:0% 5%}
        }
        @-moz-keyframes AnimationName {
            0%{background-position:0% 5%}
            50%{background-position:100% 96%}
            100%{background-position:0% 5%}
        }
        @keyframes AnimationName {
            0%{background-position:0% 5%}
            50%{background-position:100% 96%}
            100%{background-position:0% 5%}
        }

    </style>

    {!! Form::open(['route' => 'admin::git.switchBranch', 'method' => 'POST', 'class'=>'form-inline']) !!}
        <select class="selectpicker"  id="" data-live-search="true" name="branch" tabindex="1">
            @foreach($gitRemoteBranches as $infoStringBranch)
                <?php
                    $branchInfo = explode('?', $infoStringBranch); //
                    $branchName = explode('/', $branchInfo[1])[3]; // remote branch name
                ?>
                @if($branchName !== 'HEAD')
                        <option data-tokens="{{$branchName}}" value="{{$branchName}}">{{$branchName}}</option>
                @endif
            @endforeach
        </select>
        {!! Form::submit('JUST DO IT!!!', ['class' => 'btn rainbow']) !!}
    {!! Form::close() !!}

    <hr>
    <div class="topcorner">
        <div>Текущая ветка {{$currentBranch}}</div>
    </div>
    <hr>
    <div class="row">
        <div class="col-md-4">
            {!! Form::open(['route' => 'admin::git.make', 'method' => 'POST', 'class'=>'form-inline']) !!}

                {!! Form::submit('MAKE !!!', ['class' => 'btn btn-danger']) !!}
            {!! Form::close() !!}
        </div>
    </div>


@endsection