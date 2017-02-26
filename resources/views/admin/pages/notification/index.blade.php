@extends('layouts.admin')



@section('content')
    <div class="wrapper">

        <div class="well">
            {!! Form::open(['route' => ['admin::notification.store'], //TODO вставить норм ссылку
                'method'=>'POST',
                'enctype'=>'multipart/form-data',
                'class'=>'form-inline',]) !!}

                <div class="form-group">
                    <label for="organizationType">Роль користувача</label><br>
                    {!!  Form::select('organizationType', [null,'customer' => 'замовник', 'supplier' => 'учасник'], null, ['class' => 'form-control'])  !!}

                </div>

                <div class="form-group">
                    <label for="mode">Режим роботи</label><br>
                    {!!  Form::select('mode', [null,'customer' => 'тестовый', 'supplier' => 'реальний'], null, ['class' => 'form-control'])  !!}
                </div>

                <div class="form-group">
                    <label for="mode">Статус закупівлі</label><br>
                    {!!  Form::select('tenderStatus', $tenderStatus, null, ['class' => 'form-control'])  !!}

                </div>

                <div class="form-group">
                    <label for="mode">Статус пропозиції</label><br>
                    {!!  Form::select('bidStatus', [ null,
                                                    'active' => 'active',
                                                    'unsuccessful' => 'unsuccessful',
                                                    'pending' => 'pending',
                                                    'invalid.pre-qualification' => 'invalid.pre-qualification'], null, ['class' => 'form-control'])  !!}
                </div>

                <div class="clearfix"></div>

                <div class="row" style="margin-top: 20px">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="mode">Тема повідомлення</label><br>
                            {!!  Form::text('title', null, ['class' => 'form-control', 'required'])!!}
                        </div>
                    </div>
                    <div class="col-md-12" style="margin-top: 20px">
                        <div class="form-group">
                            <label for="mode">Повідомлення</label><br>
                            {!!  Form::textarea('text', null, ['class' => 'form-control', 'required']) !!}
                        </div>

                    </div>

                    <div class="form-group" style="margin-top: 20px">
                        <div class="col-sm-10">
                            {!! Form::submit(Lang::get('keys.send'),['class'=>'form-control btn btn-info']) !!}
                        </div>
                    </div>
                </div>

            {!! Form::close() !!}
        </div> {{-- end of well--}}
    </div> {{-- end of wrapper--}}

@endsection