@extends('layouts.index')

@section('content')
    {{--Editing Section Start--}}
    <section class="registration container">
        <h2>Дані організації</h2>
        @if($errors->has())
            <div class="alert alert-danger" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        {!! Form::open(['route' => 'organization.store',
                        'method'=>'POST',
                        'class'=>'form-horizontal',]) !!}
        <fieldset>


            @include('pages.organization.component.form')

            <hr>
            <script type="text/javascript">
            ga('send', 'pageview', '/regOrgDone');
            </script>
            <div class="form-group">
                <div class="col-lg-12">
                    {!! Form::submit(Lang::get('keys.save'),['class'=>'btn btn-primary pull-right']) !!}
                </div>
            </div>
        </fieldset>
        {!! Form::close() !!}
    </section>
    {{--Editing Section End--}}
@endsection