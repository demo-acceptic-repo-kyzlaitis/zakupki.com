@extends('layouts.admin')

@section('content')


    <h2>Баланс</h2>
    <div class="well">
        {!! Form::open(['url' => route('admin::payments.index'),
                'method'=>'GET',
                'class'=>'form-inline']) !!}
        <div class="form-group">
            <label for="type">ID</label><br>
            <input  class="form-control" type="text" size="50" name="form[organization_id]" value="{{$form['organization_id']}}">
        </div>
        <div class="form-group">
            <label for="type">ЕДРПОУ</label><br>
            <input  class="form-control" type="text" name="form[code]" value="{{$form['code']}}">
        </div>
        <div></div>
        <div class="form-group">
            <label>&nbsp;</label><br>
            <button type="submit" class="btn btn-primary">{{Lang::get('keys.search')}}</button>
        </div>
        {!! Form::close() !!}
    </div>

    @if($organizations->count())
        <div class="wrapper">
            <div class="">
                <p>Знайдено: {{$organizations->total()}}</p>
                <table  class="table table-striped table-bordered sortable tablesorter">
                    <thead>
                    <tr>
                        <th>Id</th>
                        <th>Назва</th>
                        <th>Користувач</th>
                        <th>Код</th>
                        <th>Баланс</th>
                        <th>Підстава</th>
                        <th>Платіжна система</th>
                        <th>Дії</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($organizations as $organization)
                        @if ($organization->user)
                        <tr>
                            <td>{{$organization->id}}</td>
                            <td>{{$organization->name}}</td>
                            <td>{{$organization->user->email}}</td>
                            <td>{{$organization->identifier}}</td>
                            <td>@if($organization->user->balance) {{$organization->user->balance->amount}} @else 0 @endif </td>
                            <td>
                                {!! Form::open(['url' => route('admin::payments.add'),
                                       'method'=>'POST',
                                       'class'=>'form',
                                       'id' => 'addTransactionForm']) !!}
                                <div class="form-group">
                                    <input  class="form-control input-sm" type="text" size="5" name="comment">
                                </div>
                            </td>
                            <td>{!!  Form::select('payment_service', $ps, null, ['class' => 'form-control pay_service'])  !!} </td>
                            <td style="width: 150px">
                                <a href="#" class="btn btn-xs btn-info add-amount"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span></a>
                                <div class="add-form" @hide >
                                    <div class="form-group input-group" >
                                        <input  type="hidden" name="user_id" value="{{$organization->user->id}}">
                                        <input  type="number" class="form-control input-sm" style="border-radius: 3px"  size="5" name="amount">
                                        <span class="input-group-btn">
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="this.disabled=true; this.form.submit();">{{Lang::get('keys.ok')}}</button>
                                        </span>
                                    </div>
                                </div>
                                <a href="#" class="btn btn-xs btn-warning  remove-amount"><span class="glyphicon glyphicon-minus" aria-hidden="true"></span></a>
                                <div class="add-form" @hide >
                                    <div class="form-group input-group" >
                                        <input  type="hidden" name="user_id" value="{{$organization->user->id}}">
                                        <input  type="number" class="form-control input-sm" style="border-radius: 3px" size="5" name="amount-minus">
                                        <span class="input-group-btn">
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="this.disabled=true; this.form.submit()">{{Lang::get('keys.ok')}}</button>
                                        </span>
                                    </div>
                                    {{--{!! Form::close() !!}--}}

                                </div>
                                {!! Form::close() !!}
                            </td>
                        </tr>
                        @endif
                    @endforeach
                    </tbody>

                </table>

            </div>

            <div class="text-center">
                {!! $organizations->appends(['form' => $form])->render() !!}
            </div>
        </div>

    @else
        <div class="text-center">За Вашим запитом нічого не знайдено</div>
    @endif

    <script>
        $(function(){
            $('.add-amount').click(function () {
                $(this).next().show();
                $(this).next().next().hide();
                $(this).hide();
                return false;
            })
        });
        $(function(){
            $('.remove-amount').click(function () {
                var action = $('.form').attr('action');
                $('.form').attr('action',action.substr(0,action.length - 3)+'removeCash');
                var finalAction = 'removeCash';
                $(this).hide();
                $(this).prev().prev().hide();
                $(this).next().show();

                return false;
            })
        });

        {{--$('#addTransactionForm').submit(function() {--}}
            {{--$.ajax({--}}
                {{--url: "{{route('admin::payments.add')}}",--}}
                {{--type:"POST",--}}
                {{--data: $('#addTransactionForm').serialize(),--}}
                {{--success: function(data){--}}
                    {{--$.notify({--}}
                        {{--title: "Поповненя пройшло успішно",--}}
                        {{--message: ""--}}
                    {{--},{--}}
                        {{--//settings--}}
                        {{--type: 'success',--}}
                    {{--});--}}
                    {{--$('.add-form').hide();--}}
                    {{--$('.add-amount').show();--}}
                {{--},--}}
                {{--error: function (request, status, error) {--}}
                    {{--$.notify({--}}
                        {{--title: "Поповнення не пройшло"--}}
                    {{--},{--}}
                        {{--type: 'danger',--}}
                        {{--animate: {--}}
                            {{--enter: 'animated bounceIn',--}}
                            {{--exit: 'animated bounceOut'--}}
                        {{--}--}}
                    {{--});--}}
                {{--}--}}
            {{--});--}}
            {{--return false;--}}
        {{--});--}}
    </script>
@endsection



