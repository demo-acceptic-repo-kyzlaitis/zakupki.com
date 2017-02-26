@extends('layouts.admin')

@section('content')
<P>История релогирования администраторами</P>
    <div class="well">
        {!! Form::open(['url' => route('admin::relogin.index'),
                'method'=>'GET',
                'class'=>'form-inline', 'id'=>'search-org-form']) !!}

        <div class="form-group">
            <label for="type">ID Администратора</label><br>
            <input  class="form-control" type="text" size="10" name="form[nominal_user]" value="{{$form['nominal_user']}}">
        </div>
    <div class="form-group">
        <label for="type">ID юзера</label><br>
        <input  class="form-control" type="text" size="10" name="form[to_user]" value="{{$form['to_user']}}">
    </div>

        <div class="form-group">
            <label>&nbsp;</label><br>
            <button type="submit" class="btn btn-primary">{{Lang::get('keys.search')}}</button>
        </div>
    </div>
        {!! Form::close() !!}


    @if($reloginHistory->count())
        <div class="wrapper">
            <div class="">
                <p>Знайдено: {{$reloginHistory->total()}}</p>
                <table class="table table-striped table-bordered sortable tablesorter table-notes">
                    <thead>
                    <tr>
                        <th>Id</th>
                        <th>ID Администратора</th>
                        <th>ID юзера</th>
                        <th>Действие</th>
                        <th>Сущность</th>
                        <th><a href="{{route('admin::relogin.index', ['so' => $sortOrder, 'sf' => 'created_at'])}}">Дата змін @if ($sortOrder == 'asc') &#9652; @else &#9662;  @endif</a>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($reloginHistory as $itemHistory)
                            <tr @if ($itemHistory->confirmed == 0) class="danger" @endif>
                                <td>{{$itemHistory->id}}</td>
                                <td>{{$itemHistory->nominal_user}}</td>
                                <td>{{$itemHistory->to_user}}</td>
                                <td>{{$itemHistory->action}}</td>
                                <td>{{$itemHistory->entity}}</td>
                                <td>{{$itemHistory->created_at}}</td>
                            </tr>

                    @endforeach
                    </tbody>

                </table>

            </div>

            <div class="text-center">
                {!! $reloginHistory->appends(['form' => $form,'so'=>$sortOrder,'pag'=> 'true'])->render() !!} <?php //?>
            </div>
        </div>

    @else
        <div class="text-center">За Вашим запитом нічого не знайдено</div>
    @endif
@endsection



