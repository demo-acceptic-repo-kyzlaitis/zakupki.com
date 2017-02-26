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
        @if (Auth::check() && $tender->isOwner(Auth::user()->id) && $tender->status == 'active.stage2.pending')
            <div class="container">
                <div class="alert alert-success" role="alert">
                    Щоб завершити цей етап і перейти до другого, натисніть кнопку
                    <a class="btn btn-success" href="{{route('tender.completeFirstStage', $tender->id)}}">{{Lang::get('keys.complete_first_stage')}}</a>
                </div>
            </div>
        @endif
        @if (Auth::check() && $tender->isOwner(Auth::user()->id))

            @if ($tender->allQuestions()->notAnswered()->count() > 0)
                <div class="container">
                    <div class="alert alert-danger" role="alert">
                        У цій закупівлі є питання без відповіді. @if(strtotime($tender->enquiry_end_date) < time())Продовжіть тендер, щоб відповісти @endif
                    </div>
                </div>
            @endif
            @if ($tender->hasNotAnsweredComplaints())
                <div class="container">
                    <div class="alert alert-danger" role="alert">
                        У цій закупівлі є вимоги або скарги без відповіді.
                    </div>
                </div>
            @endif
            @if (($tender->procedureType->threshold_type == 'above') && !$tender->signed)
                <div class="container">
                    <div class="alert alert-danger" role="alert">
                        Закупівлю потрібно підписати ЕЦП
                    </div>
                </div>
            @endif

        @endif
        @if ($tender->blocked)
            <div class="container">
                <div class="alert alert-danger" role="alert">
                    Процедура заблокована
                </div>
            </div>
        @endif
        @if ($tender->hasAnyAcceptedComplaints())
            <div class="container">
                <div class="alert alert-danger" role="alert">
                    Органом оскарження було прийнято скаргу до розгляду. У цей період
                    замовнику забороняється вчиняти будь-які дії та приймати будь-які рішення щодо закупівлі,у тому
                    числі, укладення договору про закупівлю, крім дій, спрямованих на усунення порушень, зазначених у скарзі
                </div>
            </div>
        @endif
        <div class="row">
            <div class="col-md-7">
                <h4>Загальна інформація</h4>
                <table class="clean-table">
                    <tr>
                        <th>Узагальнена назва закупівлі:</th>
                        <td>{{$tender->title}}</td>
                    </tr>
                    @if($tender->procedureType->procurement_method_type == 'aboveThresholdEU')
                        <tr>
                            <th>Узагальнена назва закупівлі (англ.):</th>
                            <td>{{$tender->title_en}}</td>
                        </tr>
                    @endif

                    <tr>
                        <th>Статус:</th>
                        <td>
                            <span class="label label-{{$tender->statusDesc->style}}">{{$tender->statusDesc->description}}</span>
                            @if (Auth::check() && Auth::user()->super_user)
                                <a href="{{route('tender.sync', [$tender->id])}}"><span
                                            class="glyphicon glyphicon glyphicon-refresh" aria-hidden="true"></span></a>
                            @endif
                        </td>
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
                    @if ($tender->multilot)
                        <tr>
                            <th>Аукціон:</th>
                            <td>Окремо по кожному лоту</td>
                        </tr>

                    @elseif ($tender->auction_url != '')
                        <tr>
                            <th>Аукціон:</th>
                            {{-- для supplier показывать ссылку на аукцион учасника--}}
                            @if(Auth::check() && Auth::user()->organization->bids()->where('tender_id', $tender->id)->first())
                                <td>
                                    <a href="{{ Auth::user()->organization->bids()->where('tender_id', $tender->id)->first()->participation_url }}">
                                        Перейти до аукціону
                                    </a>
                                </td>
                            @else
                                <td><a href="{{$tender->auction_url}}" class="move-to-auction-page">Перейти до аукціону</a></td>
                            @endif
                        </tr>
                    @endif

                    @if ($tender->multilot)
                        <tr>
                            <th>Вид тендерного забезпечення: </th>
                            <td>Окремо по кожному лоту</td>
                        </tr>
                    @else
                        @if($tender->lots[0]->guarantee_amount !== null)
                            <tr>
                                <th>Вид тендерного забезпечення: </th>
                                <td>Електронна банківська гарантія</td>
                            </tr>
                            <tr>
                                <th>Сума тендерного забезпечення:</th>
                                <td>{{$tender->lots[0]->guarantee_amount}} {{$tender->lots[0]->guaranteeCurrency->code}}</td>
                            </tr>
                        @else
                            <tr>
                                <th>Вид тендерного забезпечення: </th>
                                <td>Відсутнє</td>
                            </tr>
                        @endif
                    @endif


                    @if ($tender->multilot == 0 && isset($tender->auction_start_date))
                        <tr>
                            <th>Дата початку аукціону</th>
                            <td>{{$tender->auction_start_date}}</td>
                        </tr>
                    @endif
                    <tr>
                        <th>Очікувана вартість закупівлі:</th>
                        <td>@if($tender->lots->sum('amount') == 0 || $tender->lots->sum('amount') === NULL) <span class="data-amount">{{$tender->amount}}</span> @else <span class="data-amount">{{$tender->lots->sum('amount')}}</span>  @endif <span class="data-currency_code">{{$tender->currency->currency_code}}</span> @if ($tender->tax_included)
                                <span class="data-tax_included" data-tax="1">(Враховуючи ПДВ)</span> @else &ensp;<span class="data-tax_included" data-tax="0">(Не враховуючи ПДВ)</span> @endif</td>
                    </tr>
                    @if ($tender->minimal_step > 0)
                        <tr>
                            <th>Розмір мінімального кроку пониження ціни:</th>
                            <td><span class="data-minimal_step">{{$tender->minimal_step}}</span> <span class="data-minimal_step-currency_code">{{$tender->currency->currency_code}}</span></td>
                        </tr>
                    @endif
                </table>
                <h4>Дати</h4>
                <table class="clean-table ">
                    @if($tender->enquiry_end_date)
                        <tr>
                            <th>Закінчення періоду уточнень:</th>
                            <td class="tenderPeriod"> <span>{{$tender->enquiry_end_date}}</span></td>
                        </tr>
                    @endif
                    @if(!is_null($tender->complaint_date_end) && $tender->procedureType->threshold_type == 'above')

                        <tr>
                            <th>Оскарження умов закупівлі:</th>
                            <td class="tenderPeriod">до <span>{{$tender->complaint_date_end}}</span></td>
                        </tr>
                    @endif
                    <tr>
                        <th>Кінцевий строк подання тендерних пропозицій:</th>
                        <td class="tenderPeriod"> <span>{{$tender->tender_end_date}}</span></td>
                    </tr>
                </table>


            </div>
            <div class="col-md-5">
                <div class="col-md-12 well">

                    <h4>Контактні дані</h4>
                    <table class="clean-table ">
                        <tr>
                            <th>Назва організації:</th>
                            <td class="item-procuringEntity.name">{{$tender->organization->name}}</td>
                        </tr>
                        @if($tender->procedureType->procurement_method_type == 'aboveThresholdEU' ||
                        $tender->procedureType->procurement_method_type == 'aboveThresholdEU' ||
                        $tender->procedureType->procurement_method_type == 'competitiveDialogueEU')
                            <tr>
                                <th>Назва організації EN:</th>
                                <td class="item-procuringEntity.name">{{$tender->organization->legal_name_en}}</td>
                            </tr>
                        @endif
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
                    <table class="clean-table">
                        {{--
                            если тендер создавался у нас на площадке то контактная инфа хранится в таблице tenders
                            если с ддругой площадки то в таблице organizations
                        --}}
                        @if(!empty($tender->organization) && $tender->organization->source == 0)
                            @if (!empty($tender->contact_name))
                                <tr>
                                    <th>Ім'я:</th>
                                    <td>{{$tender->contact_name}}</td>
                                </tr>
                            @endif
                            @if($tender->procedureType->procurement_method_type == 'aboveThresholdEU' ||
                            $tender->procedureType->procurement_method_type == 'aboveThresholdEU' ||
                            $tender->procedureType->procurement_method_type == 'competitiveDialogueEU')
                                <tr>
                                    <th>Ім'я EN:</th>
                                    <td>{{$tender->contact_name_en}}</td>
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
                            @if(!empty($tender->contact_available_lang) && ($tender->procedureType->procurement_method_type == 'aboveThresholdEU' ||
                            $tender->procedureType->procurement_method_type == 'aboveThresholdEU' ||
                            $tender->procedureType->procurement_method_type == 'competitiveDialogueEU'))
                                <tr>
                                    <th>Мова:</th>
                                    <td>{{$language[$tender->contact_available_lang]}}</td>
                                </tr>
                            @endif
                        @else
                            @if (!empty($tender->organization->contact_name))
                                <tr>
                                    <th>Ім'я:</th>
                                    <td>{{$tender->organization->contact_name}}</td>
                                </tr>
                            @endif
                            @if($tender->procedureType->procurement_method_type == 'aboveThresholdEU' ||
                            $tender->procedureType->procurement_method_type == 'aboveThresholdEU' ||
                            $tender->procedureType->procurement_method_type == 'competitiveDialogueEU')
                                <tr>
                                    <th>Ім'я EN:</th>
                                    <td>{{$tender->organization->contact_name_en}}</td>
                                </tr>
                            @endif
                            @if (!empty($tender->organization->contact_phone))
                                <tr>
                                    <th>Телефон:</th>
                                    <td>{{$tender->organization->contact_phone}}</td>
                                </tr>
                            @endif
                            @if (!empty($tender->organization->contact_email))
                                <tr>
                                    <th>E-mail:</th>
                                    <td>{{$tender->organization->contact_email}}</td>
                                </tr>
                            @endif
                            @if(!empty($tender->contact_available_lang) && ($tender->procedureType->procurement_method_type == 'aboveThresholdEU' ||
                            $tender->procedureType->procurement_method_type == 'aboveThresholdEU' ||
                            $tender->procedureType->procurement_method_type == 'competitiveDialogueEU'))
                                <tr>
                                    <th>Мова:</th>
                                    <td>{{$language[$tender->contact_available_lang]}}</td>
                                </tr>
                            @endif
                        @endif
                    </table>
                    @if(is_object($procedureType) && ($procedureType->procurement_method_type == 'aboveThresholdEU' || $procedureType->procurement_method_type == 'competitiveDialogueEU' || $procedureType->procurement_method_type == 'aboveThresholdUA.defense') && $tender->tenderContacts->count() > 0)
                        <?php  $additionalContact = $tender->tenderContacts->last()->contact;?>
                        <h4>Додаткові контактні дані</h4>
                            @foreach($additionalContacts as $con)
                                <table class="clean-table">
                                    @if (!empty($con->contact->contact_name))
                                        <tr>
                                            <th>Ім'я:</th>
                                            <td>{{$con->contact->contact_name}}</td>
                                        </tr>
                                    @endif
                                    @if (!empty($con->contact->contact_phone))
                                        <tr>
                                            <th>Телефон:</th>
                                            <td>{{$con->contact->contact_phone}}</td>
                                        </tr>
                                    @endif
                                    @if (!empty($con->contact->contact_email))
                                        <tr>
                                            <th>E-mail:</th>
                                            <td>{{$con->contact->contact_email}}</td>
                                        </tr>
                                    @endif
                                </table>
                                <hr>
                            @endforeach
                    @endif


                    <h4>Цифровий підпис</h4>
                    @if (isset($tender))
                        @include('share.component.signature', ['entity' => $tender])
                    @endif
                </div>

            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <h4>Нецінові показники</h4>
                <?php
                $amount = 0;
                foreach ($tender->features as $feature) {
                    $amount += $feature->getAmount();
                }
                ?>
                <table class="clean-table">
                    <tr>
                        <th>Ціна:</th>
                        <td>{{100 - $tender->getGetMaxFeatureSum()}}%</td>
                    </tr>
                </table>
                <hr>
                <table class="clean-table">
                    @foreach($tender->features as $feature)
                        <tr>
                            <th>{{$feature->title}} ({{$feature->description}}) :</th>
                            <td>{{$feature->getAmount()}}%
                                <table class="clean-table" style="font-size: 90%; border-top: 1px solid #eee">
                                    @foreach($feature->values as $value)
                                        <tr>
                                            <th>{{$value->title}}:</th>
                                            <td>{{$value->value}}%</td>
                                        </tr>
                                    @endforeach
                                </table>
                            </td>
                        </tr>
                    @endforeach
                </table>
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
                            <div class="col-md-12">
                                <div style="background: #eee; padding: 10px; margin-bottom: 15px; line-height: 2em;">
                                    <b>{{$lot->title}} @if($tender->procedureType->procurement_method_type == 'aboveThresholdEU')({{$lot->title_en}}
                                        )@endif</b>
                                    <div style="float: right;">
                                        @if ($lot->canQuestion() && (Auth::check() && !$tender->isOwner(Auth::user()->id)) && Auth::user()->organization->type == 'supplier')
                                            <a href="{{route('questions.create', ['lot', $lot->id])}}" class="btn btn-sm btn-success">{{Lang::get('keys.create_question')}}</a>
                                        @endif
                                        @if ($lot->canQuestion() && (Auth::check() && !$tender->isOwner(Auth::user()->id)) && Auth::user()->organization->type == 'supplier')
                                            <a href="{{route('claim.create', ['lot', $lot->id])}}"
                                               class="btn btn-sm btn-danger">{{Lang::get('keys.create_complaint')}}</a>
                                        @endif
                                        @if ($lot->canCancel() && (Auth::check() && $tender->isOwner(Auth::user()->id)))
                                            <a href="{{route('cancel.create', ['lot', $lot->id])}}" id="lot-cancel" class="btn btn-sm btn-danger">{{Lang::get('keys.cancel_lot')}}</a>
                                        @endif
                                        @if ($lot->canBid() && $tender->multilot && (Auth::check() && Auth::user()->organization->type == 'supplier'))
                                            @if ($lot->organizationBids(Auth::user()->organization->id)->count() > 0)
                                                <a href="{{route('bid.edit', [$lot->organizationBids(Auth::user()->organization->id)[0]->id])}}" class="btn btn-sm btn-success">{{Lang::get('keys.edit_bid')}}</a>
                                            @elseif ($tender->status != 'active.qualification')
                                                <a href="{{route('bid.new', ['lot', $lot->id])}}" class="btn btn-sm btn-success">{{Lang::get('keys.create_bid')}}</a>
                                            @endif
                                        @endif
                                        @if(!$lot->canBid() && $tender->multilot && (Auth::check() && Auth::user()->organization->type == 'supplier'))
                                            <?php $organizationBids = $lot->organizationBids(Auth::user()->organization->id);?>
                                            @if ($organizationBids && isset($organizationBids[0]))
                                                <a href="{{route('bid.show', [$organizationBids[0]->id])}}" class="btn btn-sm btn-success">{{Lang::get('keys.get_bid')}}</a>
                                            @endif
                                        @endif
                                        @if (!Auth::check())
                                            <a href="{{route('bid.new', ['lot', $lot->id])}}" class="btn btn-sm btn-success">{{Lang::get('keys.create_bid')}}</a>
                                        @endif
                                    </div>
                                    <div style="clear: both; float: none;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <ul class="nav nav-tabs" role="tablist">
                                    <li role="presentation" class="active"><a href="#lot-{{$index}}" aria-controls="lot-{{$index}}" role="tab" data-toggle="tab">Список товарів</a></li>
                                    <li role="presentation" class="@if ($lot->documents->count() == 0) disabled @endif" ><a @if ($lot->documents->count() > 0) href="#lotdocs-{{$index}}" aria-controls="lotdocs-{{$index}}" role="tab" data-toggle="tab" @endif>Документація по лоту</a></li>
                                    <li role="presentation" class="@if ($lot->questions->count() == 0) disabled @endif">
                                        <a @if ($lot->questions->count() > 0) href="#lotquestions-{{$index}}" aria-controls="lotquestions-{{$index}}" role="tab" data-toggle="tab" @endif>Запитання ({{$lot->questions->count()}})</a></li>
                                </ul>
                            </div>
                        </div>
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
    @include('share.component.modal-ecp')
@endsection
