@extends('layouts.index')

@section('content')
    {{--Editing Section Start--}}
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <?php $tender = $entity->tender;?>
                @include('share.component.title')
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                @include('share.component.tabs')
            </div>
        </div>
        <section class="registration container">
            <h4>Реєстрація пропозиції</h4>
            @if (isset($bid))
                <div class="row">
                    <div class="col-md-12">
                        @include('share.component.signature', ['entity' => $bid])
                    </div>
                </div>
            @endif
            @if($errors->has())
                <div class="alert alert-danger" role="alert">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            {!! Form::model($bid,['route' => ['bid.update', $bid->id],
            'method'=>'PUT',
            'enctype'=>'multipart/form-data',
            'class'=>'form-horizontal']) !!}
            <fieldset>
                <input type="hidden" id="bid-id" data-bid-id="{{$bid->id}}">

                @include('pages.bids.component.form')

                <hr>
                <div class="form-group">
                    <div class="col-lg-12 text-center">
                        {!! Form::submit('Оновити',['class'=>'btn btn-primary']) !!}
                        <a href="#" class="btn btn-success" data-toggle="modal" data-target="#signature-set" data-id="{{$bid->id}}" data-documentname="bid">{{Lang::get('keys.sign')}}</a>
                        @if ($bid->tender->status != 'active.qualification')
                            <a href="#" data-toggle="modal"
                                 data-target="#modal-confirm-bid"
                                 class="btn btn-danger">{{Lang::get('keys.cancel')}}</a>
                        @endif
                    </div>
                </div>
            </fieldset>
            {!! Form::close() !!}

            {!! Form::model($bid, ['route' => ['bid.delete', $bid->id], 'method' => 'DELETE', 'id' => 'id-cancel-bid-form']) !!}

            @include('share.component.modal-form-confirm', ['modalId' => 'bid', 'modalTitle' => 'Відміна пропозиції', 'modalMessage' => 'Ви впевнені, що хочете відмінити свою пропозицію? Якщо так, то Ваша пропозиція буде видалена з центральної бази даних і Ви не зможете приймати участь в цій закупівлі.'])

            {!! Form::close() !!}

        </section>

        @if(count($history) > 0)
            <div class="row">
                <div class="col-md-12">
                    @include('share.component.history', [
                                'history'       => $history,
                                'tableHeadings' => ['Сума', 'Дата оновлення'],
                                'historyName'   => 'Історія зміни пропозиції',
                             ])
                </div>
            </div>
        @endif
        <hr>
        @if(count($featureHistory))
            <div class="row">
                <div class="col-md-12">
                    @include('share.component.history', [
                                'history'       => $featureHistory,
                                'tableHeadings' => ['Неціновий показник', 'Дата оновлення'],
                                'historyName'   => 'Історія зміни нецінових показників',
                            ])
                </div>
            </div>
        @endif

        {{--Editing Section End--}}
    </div>
    @include('share.component.modal-ecp')
@endsection
