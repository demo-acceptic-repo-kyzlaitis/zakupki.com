@extends('layouts.index')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                @include('share.component.title')
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                @include('share.component.buttons')
                @include('share.component.tabs')
            </div>
        </div>
        <h2>Пропозицї на розгляді</h2><hr>
        @if($tender->allQualifications->count())
            <div class="row">
                <div class="col-md-12">
                <table  class="table table-striped table-bordered">
                    <tr>
                        <th>Назва</th>
                        <th>Лот</th>
                        <th>ЕДРПОУ</th>
                        <th>Статус</th>
                        <th></th>
                    </tr>
                    @foreach($tender->allQualifications as $qualification)
                        <tr>
                            <td>{{$qualification->bid->organization->name}}</td>
                            <td>{{$qualification->bid->bidable->title}}</td>
                            <td>{{$qualification->bid->organization->identifier}}</td>
                            <td>
                                @if($qualification && $qualification->status == 'active')
                                    <span class="label label-success">Пропозицію допущено до аукціону</span>
                                @elseif($qualification->status == 'unsuccessful')
                                    <span class="label label-danger">Пропозицію не допущено до аукціону</span>
                                @else
                                    <span class="label label-warning">Пропозицію не розглянуто</span>
                                @endif
                            </td>
                            <td>
                                @if($tender->status == 'active.pre-qualification' && $tender->isOwner())
                                    <a class="btn btn-success" href="{{route('bid.qualification',[$qualification->id])}}">{{Lang::get('keys.qualification')}}</a>
                                @endif
                            </td>
                        </tr>

                    @endforeach
                </table>
                    </div>
                @if($tender->isOwner() && $tender->status == 'active.pre-qualification')

                    {!! Form::open(['url' => "/tender/{$tender->id}/qualify",
                            'method'=>'POST',
                            'class'=>'form-horizontal',]) !!}
                    {!! Form::hidden('error_qualify', 'y', ['class' => 'form-control', 'placeholder' => '', 'required'])  !!}

                    <div class="form-group">
                        <div class="col-lg-12 text-center">
                            <a href="#" data-toggle="modal"
                               data-target="#modal-confirm-tender-qualify"
                               class="btn btn-danger">{{Lang::get('keys.qualification_end')}}</a>
                        </div>
                    </div>

                    @include('share.component.modal-form-confirm', ['modalId' => 'tender-qualify', 'modalTitle' => 'Закінчення кваліфікації', 'modalMessage' => 'Ви впевнені, що хочете закінчити кваліфікацію?'])

                    {!! Form::close() !!}
                @endif
            </div>

        @endif
    </div>
@endsection



