@extends('layouts.admin')

@section('content')


    <h2>Вгрузка из клиент-банка</h2>
    <div class="well">
        <form action="{{route('admin::payments.upload')}}" method="post" enctype="multipart/form-data">
            {{csrf_field()}}
            CSV: <input type="file" name="payments"><br>
            <button type="submit" class="btn btn-success">{{Lang::get('keys.download')}}</button>
        </form>
    </div>

@endsection



