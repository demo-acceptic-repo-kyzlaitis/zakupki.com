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
        @if ($tender->procedureType->procurement_method_type != 'reporting')
            <div>
                @if (!$tender->winner)
                    <a href="{{route('award.define', ['tender' => $tender->id])}}" class="btn btn-warning">{{Lang::get('keys.add_customer')}}</a>
                @endif
            </div>
        @endif
        <table class="table table-hover">
            <thead>
            <tr>
                <th>№</th>
                <th>Назва</th>
                <th>Статус</th>
                <th>Дії</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $winner = false;
            foreach ($awards as $award) {
                if ($award->status == 'active') $winner = true;
            }
            ?>
            @foreach($awards as $index => $award)
                <tr>
                    <td>{{$index + 1}}</td>
                    <td>{{$award->organization->name}}</td>
                    <td><span class="label label-{{$award->statusDesc->style}}">{{$award->statusDesc->description}}</span></td>
                    <td>
                        @if ($award->status == 'pending' && !$winner)
                            <a href="{{route('award.edit', [$award->id])}}" class="btn btn-xs btn-info helper" title-data="Редагування"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></a>
                            <?php
                            $hasDocs = $award->documents()->where('format', '!=', 'application/pkcs7-signature')->count();
                            ?>
                            @if ($tender->procedureType->procurement_method_type != 'reporting' && $hasDocs)
                                <a href="{{route('bid.reject', [$award->id])}}" class="btn btn-xs btn-danger">{{Lang::get('keys.bid')}}</a>
                                <a href="{{route('bid.confirm', [$award->id])}}" class="btn btn-xs btn-success helper" title-data="Визначити переможця">{{Lang::get('keys.winner')}}</a>
                            @elseif(!$hasDocs && $tender->procedureType->procurement_method_type != 'reporting')
                                <span class="text-danger">Додайте документи для подальших дій</span>
                            @endif
                        @endif
                    </td>
                </tr>
            @endforeach

            </tbody>
        </table>
    </div>
    <script>

        $('body').on('click', '.skarga', function () {
            var result = confirm('Зверніть увагу на те, що оскарженню підлягає лише рішення по визначенню переможця переговорів, оскаржувати учасників переговорів заборонено');
            if(result == true ){
                return true;
            }else{
                return false;
            }
        });

    </script>
@endsection