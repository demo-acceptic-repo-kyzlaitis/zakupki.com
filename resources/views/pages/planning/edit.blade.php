@extends('layouts.index')

@section('content')


    {{--Editing Section Start--}}
    <section class="registration container">
        <h2>Редагування плану закупівель</h2>
        {{--<input type="hidden" id="isEditable" data-editable="true">--}}
        <div class="row">
            <div class="col-md-1"></div>
            <div class="col-md-9">
                @if (isset($plan))
                    @include('share.component.signature', ['entity' => $plan])
                @endif

                @if($errors->has())
                    <div class="alert alert-danger" role="alert">
                        <ul>
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

                    {!! Form::model($plan,['route' => ['plan.update', $plan->id],
                    'method'=>'PUT',
                    'enctype'=>'multipart/form-data',
                    'class'=>'form-horizontal', 'id' => 'edit-form-submit']) !!}

                    @include('pages.planning.component.form')

                    <hr>
                    <div class="form-group">
                        <div class="col-lg-12 text-center">
                            {!! Form::submit('Зберегти',['class'=>'btn btn-lg btn-danger', 'id' => 'submit-edit-btn-plan']) !!}
                            <a href="#" class="btn btn-lg btn-success" data-toggle="modal" data-target="#signature-set" data-id="{{$plan->id}}" data-documentname="plan">{{Lang::get('keys.sign_ecp')}}</a>
                        </div>
                    </div>
                </fieldset>
                {!! Form::close() !!}
                    @include('share.component.modal-ecp')
            </div>
            <div class="col-md-1"></div>
        </div>
    </section>
    {{--Editing Section End--}}
@endsection