@extends('layouts.index')
@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                @include('share.component.title')
            </div>
        </div>
        @if($errors->has())
            <div class="container">
                <div class="alert alert-danger" role="alert" >
                    <ul id="errors">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
        <div class="row">
            <div class="col-md-12">
                @include('share.component.buttons')
                @include('share.component.tabs')
            </div>
        </div>
        @if (Auth::check() && $tender->isOwner(Auth::user()->id))

            @if ($tender->allQuestions()->notAnswered()->count() > 0 && $tender->canQuestion())
                <div class="container"><div class="alert alert-danger" role="alert">
                        У цій закупівлі є питання без відповіді.
                    </div></div>
            @endif
            @if ($tender->complaints()->where('status', 'pending')->count() > 0)
                <div class="container"><div class="alert alert-danger" role="alert">
                        У цій закупівлі є скарги без відповіді.
                    </div></div>
            @endif
                @if ($tender->procedureType->threshold_type == 'above.limited' && !$tender->signed)
                    <div class="container">
                        <div class="alert alert-danger" role="alert">
                            Закупівлю потрібно підписати ЕЦП
                        </div>
                    </div>
                @endif


        @endif
        <div class="row">
            <div class="col-md-7">
                <h4>Загальна інформація</h4>
                <table class="clean-table">
                    <tr>
                        <th>Статус:</th>
                        <td><span class="label label-{{$tender->statusDesc->style}}">{{$tender->statusDesc->description}}</span></td>
                    </tr>
                    @if($tender->status == 'unsuccessful')
                        <tr>
                            <th>Причина відміни:</th>

                            <td>Процедура була відмінена за недостатьною кількістю пропозицій</td>

                        </tr>
                    @endif

                    <tr>
                        <th>ID:</th>
                        <td>{{$tender->tenderID}}</td>
                    </tr>
                    @if (Auth::check() && Auth::user()->super_user)
                        <tr>
                            <th>Source:</th>
                            <td><a href="{{env('PRZ_API')}}/tenders/{{$tender->cbd_id}}">source</a></td>
                        </tr>
                    @endif
                    <tr>
                        <th>Процедура:</th>
                        <td>{{$tender->procedureType->procedure_name}}</td>
                    </tr>
                    <tr>
                        <th>Ціна пропозиції :</th>
                        <td><span class="data-amount">{{$tender->amount}}</span> <span class="data-currency_code">{{$tender->currency->currency_code}}</span> @if ($tender->tax_included) <span class="data-tax_included" data-tax="1">(Враховуючи ПДВ)</span> @else &ensp;<span class="data-tax_included" data-tax="0">(Не враховуючи ПДВ)</span> @endif</td>
                    </tr>
                </table>


            </div>
            <div class="col-md-5">
                <div class="col-md-12 well">

                    <h4>Контактні дані</h4>
                    <table  class="clean-table ">
                        <tr>
                            <th>Назва організації:</th>
                            <td class="item-procuringEntity.name">{{$tender->organization->name}}</td>
                        </tr>
                        <tr>
                            <th>Код ЄДРПОУ:</th>
                            <td>{{$tender->organization->identifier}}</td>
                        </tr>
                        <tr>
                            <th>Поштова адреса:</th>
                            <td>{{$tender->organization->getAddress()}}</td>

                        </tr>
                    </table>
                    <hr>
                    <table  class="clean-table">
                        @if (!empty($tender->contact_name))
                            <tr>
                                <th>Ім'я:</th>
                                <td>{{$tender->contact_name}}</td>
                            </tr>
                        @endif
                        @if (!empty($tender->contact_phone))
                            <tr>
                                <th>Телефон:</th>
                                <td>{{$tender->contact_phone}}</td>
                            </tr>
                        @endif
                        @if (!empty($tender->contact_email))
                            <tr>
                                <th>E-mail:</th>
                                <td>{{$tender->contact_email}}</td>
                            </tr>
                        @endif
                    </table>
                    <h4>Цифровий підпис</h4>
                    @if (isset($tender))
                        @include('share.component.signature', ['entity' => $tender])
                    @endif
                </div>

            </div>
        </div>

        @if (isset($tender) && $tender->documents->count() > 0)
            <div class="row">
                <div class="col-md-12">
                    <h4>Документи</h4>
                    @include('share.component.document-list', ['entity' => $tender])
                </div>
            </div>
        @endif
        <div class="row">

            <div class="col-md-12">

                @if ($tender->multilot)
                    <h4>Лоти</h4>
                    @foreach($tender->lots as $index => $lot)
                        <div class="row">
                            <div class="tab-content">
                                <div role="tabpanel" class="tab-pane fade in active" id="lot-{{$index}}">
                                    @include('pages.tender.'.$template.'.lot-detail', ['lot' => $lot])
                                </div>
                                <div role="tabpanel" class="tab-pane fade" id="lotdocs-{{$index}}">

                                    @if (isset($lot))
                                        @include('share.component.document-list', ['entity' => $lot])
                                    @endif

                                </div>
                                <div role="tabpanel" class="tab-pane fade " id="lotquestions-{{$index}}">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="col-md-12">
                                                @include('pages.question.component.questions-list', ['entity' => $lot])
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <?php
                    $index = 0;
                    $lot = $tender->lots[$index];
                    ?>
                    @if ($lot->items->count() > 0)
                        <h4>Перелік товарів</h4>
                        <div class="row">
                            <div class="col-md-12">
                                <div style="background: #eee; padding: 15px; margin-bottom: 15px">&nbsp;</div>
                            </div>
                        </div>
                        <table class="table  ">
                            @include('pages.tender.'.$template.'.item-list')
                        </table>
                    @endif
                @endif
            </div>

        </div>
    </div>
        @if($tender->type_id == 5 ||  $tender->type_id == 6)
            <script type="text/javascript">
                $('body').on('click', '.skarga', function () {
                    var result = confirm('Зверніть увагу на те, що оскарженню підлягає лише рішення по визначенню переможця переговорів, оскаржувати учасників переговорів заборонено');
                    if(result == true ){
                        return true;
                    }else{
                        return false;
                    }
                });
            </script>
        @endif
    @include('share.component.modal-ecp')

@endsection
