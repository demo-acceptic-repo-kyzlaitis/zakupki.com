@extends('layouts.index')
@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h2>{{$plan->description}}</h2>
                <p style="padding: 5px 0; font-size: 14px; font-weight: bold">{{$plan->procedure->procedure_name}}</p>
                <p style="color:#999">{{$plan->notes}}</p>
            </div>
        </div>
        @if($errors->has())
            <div class="container">
                <div class="alert alert-danger" role="alert">
                    <ul id="errors">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
        <div class="row">
            <div class="col-md-7">
                <h4>Загальна інформація</h4>
                <table class="clean-table">
                    <tr>
                        <th>Узагальнена назва плану:</th>
                        <td>{{$plan->description}}</td>
                    </tr>
                    <tr>
                        <th>ID:</th>
                        <td>{{$plan->cbd_id}}</td>
                    </tr>
                    @if (Auth::check() && Auth::user()->super_user)
                        <tr>
                            <th>Source:</th>
                            <td><a href="{{env('PRZ_API')}}/tenders/{{$plan->cbd_id}}">source</a></td>
                        </tr>
                    @endif
                    <tr>
                        <th>Бюджет:</th>
                        <td>{{$plan->amount}}</td>
                    </tr>
                </table>


                @if (isset($plan->start_day))
                            <h4>Дати</h4>
                            <table class="clean-table">
                                <tr>
                                    <th>Орієнтовний початок процедури закупівлі</th>
                                    <td>{{$plan->start_date}}</td>
                                </tr>
                            </table>
                @endif

                        <h4>Класифікатори</h4>
                        <table class="clean-table">
                            <tr>
                                <th>Класифікатор ДК 021:2015</th>
                                <td>{{$plan->code->code}}{{$plan->code->description}}</td>
                            </tr>
                            @if(!empty($plan->codeKekv))
                                <tr>
                                    <th>Класифікатор КЕКВ</th>
                                    <td>{{$plan->codeKekv->description}}</td>
                                </tr>
                            @endif
                        </table>
            </div>
            <div class="col-md-5">
                <div class="col-md-12 well">

                    <h4>Контактні дані</h4>
                    <table class="clean-table ">
                        <tr>
                            <th>Назва організації:</th>
                            <td class="item-procuringEntity.name">{{$plan->organization->name}}</td>
                        </tr>
                        <tr>
                            <th>Код ЄДРПОУ:</th>
                            <td>{{$plan->organization->identifier}}</td>
                        </tr>
                        <tr>
                            <th>Поштова адреса:</th>
                            <td>{{$plan->organization->getAddress()}}</td>

                        </tr>
                    </table>
                    <hr>
                    <table class="clean-table">
                        @if (!empty($plan->organization->contact_name))
                            <tr>
                                <th>Ім'я:</th>
                                <td>{{$plan->organization->contact_name}}</td>
                            </tr>
                        @endif
                        @if (!empty($plan->organization->contact_phone))
                            <tr>
                                <th>Телефон:</th>
                                <td>{{$plan->organization->contact_phone}}</td>
                            </tr>
                        @endif
                        @if (!empty($plan->organization->contact_email))
                            <tr>
                                <th>E-mail:</th>
                                <td>{{$plan->organization->contact_email}}</td>
                            </tr>
                        @endif
                    </table>
                </div>

            </div>
        </div>



        @if(count($plan->items) > 0)
            <div class="row">
                <h4>Номенклатура</h4>
                @foreach($plan->items as $item)
                    <div class="col-md-12">
                        <div style="background: #eee; padding: 15px; margin-bottom: 15px">{{$item->description}}</div>
                        <div class="col-md-7">
                            <table class="clean-table">
                                <tr>
                                    <th>Кількість:</th>
                                    <td class="item-amount">
                                <span class="data-item-amount">
                                    {{$item->quantity}}
                                </span>
                                        <span class="data-item-amount-code">
                                    {{Lang::choice($item->unit->description, $item->quantity, [], 'ru')}}
                                </span>
                                    </td>
                                </tr>
                                @foreach($item->codes as $ci => $code)
                                    @if($item->codes[$ci]->code != '0')
                                        <tr class="data-item-classifier">
                                            <th>Класифікатор <span
                                                        class="@if ($code->classifier->alias == 'cpv') item-classification.scheme @else item-additionalClassifications.scheme @endif data-item-classifier-scheme"
                                                        data-item-classifier-scheme="{{$code->classifier->scheme}}">{{$code->classifier->name}}</span>
                                            </th>
                                            <td class="item-{{$code->classifier->alias}}"><span
                                                        class="data-item-classifier-code">{{$item->codes[$ci]->code}}</span>
                                                <span class="data-item-classifier-desc">{{$item->codes[$ci]->description}}</span>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                                @if ($item->delivery_date_start !== null || $item->delivery_date_end !== null)
                                    <tr>
                                        <th>Період доставки</th>
                                        <td class="item-delivery_period">@if ($item->delivery_date_start !== null ) з
                                            <span
                                                    class="data-item-delivery-start">{{$item->delivery_date_start}}</span> @endif @if ($item->delivery_date_end !== null)
                                                до <span
                                                        class="data-item-delivery-end">{{$item->delivery_date_end}}</span> @endif
                                        </td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif


    </div>

    <div class="modal fade" tabindex="-1" role="dialog" id="signature-set">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Закрити"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Підписати</h4>
                </div>
                <div class="modal-body">
                    <form id="sign-form" class="form-horizontal">
                        <div class="form-group">
                            <input type="hidden" class="form-control" value="{{$plan->id}}" name="SignID" id="SignID"/>
                        </div>

                        <div class="form-group">
                            <label for="SignCertFile" class="control-label col-lg-2">Сертифiкат</label>
                            <div class="col-lg-10">
                                <input type="file" class="form-control" name="SignCertFile" id="SignCertFile"/>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="SignKeyFile" class="control-label col-lg-2">Приватний ключ</label>
                            <div class="col-lg-10">
                                <input type="file" class="form-control" name="SignKeyFile" id="SignKeyFile"/>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="SignKeyPassword" class="control-label col-lg-2">Пароль ключа</label>
                            <div class="col-lg-10">
                                <input type="password" class="form-control" name="SignKeyPassword"
                                       id="SignKeyPassword"/>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-lg-offset-2 col-lg-10">
                                <button class="btn btn-primary ladda-button" data-style="expand-right">Пiдписати
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Закрити</button>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">

        $("#sign-form").submit(DoSign);
        function DoSign(e) {
            e.preventDefault();

            var idField = this["SignID"];
            var certField = this["SignCertFile"];
            var keyField = this["SignKeyFile"];
            var passwordField = this["SignKeyPassword"];

            if (!idField.value) {
                alert("No ID provided");
                return;
            }

            if (!certField.value) {
                alert("Оберіть сертифікат");
                return;
            }

            if (!keyField.value) {
                alert("Оберіть приватний ключ");
                return;
            }

            if (!passwordField.value) {
                alert("Заповніть поле пароль");
                return;
            }

            var documentId = idField.value;
            var certFile = certField.files[0];
            var keyFile = keyField.files[0];
            var password = passwordField.value;

            // show spinner
            var l = Ladda.create($("#sign-form button")[0]);
            l.start();

            SignDocument("tender", documentId, certFile, keyFile, password, function () {
                l.stop();

                $('#signature-set').modal('hide');
                alert("Підпис накладенно успішно.");
                location.reload();

            }, function (e) {
                l.stop();

                if (e.message)
                    e = e.message;
                else
                    e = e.toString();

                alert("Помилка підписання: " + e);
            });
        }
    </script>
@endsection
