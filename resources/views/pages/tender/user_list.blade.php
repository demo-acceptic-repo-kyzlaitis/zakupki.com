@extends('layouts.index')

@section('content')
    @include('share.component.modal-confirm', ['modalNamespace' => 'tender', 'modalTitle' => 'Видалення тендеру', 'modalMessage' => 'Ви справді хочете видалити тендер?'])
    <div class="container">
        <h2>Мої закупівлі</h2><hr>
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
        @if($tenders->count())
            <div class="row">
                {!! $filters !!}
                <div id="{{$listName}}">
                    @include('pages.tender._part.list', ['tenders' => $tenders])
                </div>
            </div>

            <div class="text-center">
                {!! $tenders->render() !!}
            </div>

        @else
            <div class="well text-center">
                <h2>У Вас немає створених закупівель</h2>
                <a href="{{route('tender.create')}}" class="btn btn-warning">{{Lang::get('keys.create_tender')}}</a>
            </div>
        @endif
    </div>
@endsection



