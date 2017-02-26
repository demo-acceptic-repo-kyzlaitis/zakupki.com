@extends('layouts.index')

@section('content')
    {{--Editing Section Start--}}
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                @include('share.component.title')
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                @include('share.component.tabs')
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <section class="registration container">
                    <h4>Інформація про переможця</h4>
                    @if($errors->has())
                        <div class="alert alert-danger" role="alert">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    {!! Form::open(['url' => "/award",
                    'method'=>'POST',
                    'enctype'=>'multipart/form-data',
                    'class'=>'form-horizontal create-award-form',]) !!}
                    <fieldset>
                        @include('pages.award.component.form-limited')
                        <hr>
                        <div class="form-group">
                            <div class="col-lg-12 text-center">
                                {!! Form::submit(Lang::get('keys.save'),['class'=>'btn btn-danger', 'data-toggle' => "modal", 'data-target' => "#modal-confirm-award"]) !!}
                            </div>
                        </div>
                    </fieldset>

                    @include('share.component.modal-form-confirm', ['modalId' => 'award', 'modalTitle' => 'Зміна ціни', 'modalMessage' => 'Зверніть увагу, що сума договору відрізняється від очікуванної вартості - перевірте, чи вона правильно вказана. По завершенню внесення інформації та підписання ЕЦП договору, жодних змін до закупівлі внести буде неможливо.', 'modalYesBtn' => 'Продовжити'])
                    {!! Form::close() !!}
                </section>
                {{--Editing Section End--}}
            </div>
        </div>
    </div>
@endsection