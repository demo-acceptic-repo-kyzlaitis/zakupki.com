@extends('layouts.index')

@section('content')
    <div class="container registration ">
        <div class="row">
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

            <h2>Імпортувати план у вигляді xsl</h2>
            <hr>

            <h4>Примітки</h4>
            <p>Приклад планів в xls форматі можете завантажити <a href="/shared/zrazok_planiv.xls">за посиланням.</a> </p>
            <p>Дуже важливо вказувати коди класифікаторів в колонці під номером 1 (Предмет закупівлі) в форматі 00.00.0 та 00000000-0.</p>
            <p>Дуже важливо щоб xls файл містив лише <b>ОДИН лист</b> в іншому випадку сервер поверне помилку.</p>
            <br>
            <br>
            {!!  Form::open([
                'url' => route('plan.import'),
                'method' => 'POST',
                'enctype'=>'multipart/form-data',
                'id' => 'import-plan-form',
                'class'=>'form-horizontal',
            ])!!}

            <div class="form-group">
                <label for="xlsFile" class="col-sm-2 control-label">Xls файл:</label>
                <div class="col-sm-10">
                    <input type="file" name="xlsFile" id="xlsFileId" class="form-control" value="" title="" required="required" >
                </div>
            </div>

            <div class="form-group">
                <div class="col-lg-12 text-center">
                    {!! Form::submit(Lang::get('keys.import'),['class'=>'btn btn-lg btn-danger', 'id' => 'submit-xls-import']) !!}
                </div>
            </div>

            {!! Form::close()!!}
        </div>


    </div>


@endsection