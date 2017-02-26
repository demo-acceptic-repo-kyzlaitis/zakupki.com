@extends('layouts.index')

@section('content')
    <div class="container">
        <h2>Кваліфікація</h2><hr>
        @if($tenders->count())
            <div class="row">
                <table  class="table table-striped table-bordered">
                    <tr>
                        <th>Ідентифікатор закупівлі</th>
                        <th>Найменування</th>
                        <th></th>
                    </tr>
                    @foreach($tenders as $tender)
                        <tr>
                            <td>{{$tender->tenderID}}</td>
                            <td>
                                <a href="{{route('tender.show', [$tender->id])}}">{{$tender->title}}</a>
                            </td>
                            <td>
                                <a href="{{route('award.tender', [$tender->id])}}">Перейти до вибору переможця</a>
                            </td>
                        </tr>

                    @endforeach
                </table>
            </div>

            <div class="text-center">
                {!! $tenders->render() !!}
            </div>

        @else
            <div class="well text-center">
                <h2>Тендери, що вимагають кваліфікації, відсутні</h2>
            </div>
        @endif
    </div>
@endsection



