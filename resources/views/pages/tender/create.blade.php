@extends('layouts.index')



@section('content')
    {{--Editing Section Start--}}

    <section class="registration container">
        {{--<input type="hidden" id="isEditable" data-editable="false">--}}
        <h2>Нова закупівля</h2>
        <div class="row">
            <div class="col-md-1"></div>
            <div class="col-md-9">
                @if($errors->has())
                    <div class="alert alert-danger" role="alert" >
                        <ul id="errors">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <div class="alert alert-danger errors-create" role="alert" hidden>
                        <ul id="errors">
                        </ul>
                    </div>
                @endif


                {!! Form::open(['url' => "/tender",
                'method'=>'POST',
                'enctype'=>'multipart/form-data',
                'class'=>'form-horizontal',
                'id' => 'create-tender-form']) !!}
                <fieldset>
                    {!! Form::hidden('tender_id', 0) !!}

                    @include('pages.tender.'.$template.'.form')

                    <hr>
                    <div class="form-group">
                        <div class="col-lg-12 text-center">
                            {!! Form::submit(Lang::get('keys.create'),['class'=>'btn btn-lg btn-danger', 'id' => 'submit-tender']) !!}
                        </div>
                    </div>
                </fieldset>
                {!! Form::close() !!}

            </div>
            <div class="col-md-1"></div>
        </div>
    </section>
    {{--Editing Section End--}}
@endsection