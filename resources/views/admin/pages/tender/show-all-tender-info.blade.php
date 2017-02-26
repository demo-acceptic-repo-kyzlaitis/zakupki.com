@extends('layouts.index')
@section('content')

    <div class="container">



        <h2>Ідентифікатор закупівлі {{$tender->tenderID}}</h2>
        <h4><span class="label label-{{$tender->statusDesc->style}}">{{$tender->statusDesc->description}}</span></h4>
        @include('share.component.buttons')
        <div>&nbsp;</div>
        @include('share.component.tabs')
        <h3 class="text-danger">Інформація про процедури закупівлі</h3>
        <table class="table table-striped table-bordered">
            @if (!empty($tender->cbd_id))
                <tr>
                    <th>Першоджерело в sandbox</th>
                    <td><a href="https://api-sandbox.openprocurement.org/api/0/tenders/{{$tender->cbd_id}}">{{$tender->cbd_id}}</a></td>
                </tr>
            @endif
            @if (!empty($tender->auction_url))
                <tr>
                    <th>Аукціон</th>
                    <td><a href="{{$tender->auction_url}}">{{$tender->auction_url}}</a></td>
                </tr>
            @endif
            <tr>
                <th>Дата початку періоду уточнень</th>
                <td>{{$tender->enquiry_start_date}}</td>
            </tr>
            <tr>
                <th>Дата завершення періоду уточнень</th>
                <td>{{$tender->enquiry_end_date}}</td>
            </tr>
            <tr>
                <th>Дата початку прийому пропозицій</th>
                <td>{{$tender->tender_start_date}}</td>
            </tr>
            <tr>
                <th>Кінцевий строк подання тендерних пропозицій</th>
                <td>{{$tender->tender_end_date}}</td>
            </tr>
        </table>
        <hr>
        <h3 class="text-danger">Інформація про предмет закупівлі</h3>
        <table class="table table-striped table-bordered">
            <tr>
                <th>Статус</th>
                <td>{{$tender->statusDesc()->first()->description}}</td>
            </tr>
            <tr>
                <th>Конкретна назва предмету закупівлі</th>
                <td>{{$tender->title}}</td>
            </tr>
            <tr>
                <th>Загальні відомості про закупівлю</th>
                <td>{{$tender->description}}</td>
            </tr>
            <tr>
                <th>Очікувана вартість</th>
                <td>{{$tender->amount}} {{$tender->currency->currency_code}} @if ($tender->tax_included) (Враховуючи ПДВ) @endif</td>
            </tr>
            <tr>
                <th>Розмір мінімального кроку пониження ціни</th>
                <td>{{$tender->minimal_step}}</td>
            </tr>
        </table>
        @foreach($tender->items as $i => $item)
            <h3 class="text-danger">Лот {{$i + 1}}</h3>
            <table class="table table-striped table-bordered">
                <tr>
                    <th>Опис предмету закупівлі</th>
                    <td>{{$item->description}}</td>
                </tr>
                <tr>
                    <th>Кількість</th>
                    <td>{{$item->quantity}}</td>
                </tr>
                <tr>
                    <th>Одиниця виміру</th>
                    <td>{{$item->unit->description}}</td>
                </tr>
                @foreach($item->codes as $ci => $code)
                    <tr>
                        <th>Класифікатор {{$code->classifier->name}}</th>
                        <td>{{$item->codes[$ci]->code}} {{$item->codes[$ci]->description}}</td>
                    </tr>
                @endforeach
                @if ($item->delivery_date_start !== null && $item->delivery_date_end !== null)
                    <tr>
                        <th>Період поставки</th>
                        <td>@if ($item->delivery_date_start !== null ) з {{$item->delivery_date_start}} @endif @if ($item->delivery_date_end !== null) до {{$item->delivery_date_end}} @endif</td>
                    </tr>
                @endif
                <tr>
                    <th>
                        Адреса поставки
                    </th>
                    @if ($item->same_delivery_address == 1)
                        <td>За адресою замовника</td>
                    @else
                        <td>{{$item->getAddress()}}</td>
                    @endif
                </tr>
            </table>
        @endforeach
        <hr>
        <h3 class="text-danger">Документи</h3>

        <table  class="table table-striped table-bordered">
            @if (isset($tender))
                <?php
                $docs = [];
                foreach ($tender->documents as $document) {
                    if ($document->document_parent_id != 0) {
                        $docs[$document->document_parent_id][] = $document;
                    } else {
                        $docs[$document->id][] = $document;
                    }
                }
                ?>
                @foreach($docs as $docsCont)
                    <tr>

                        <td>
                            <?php $count = count($docsCont);?>
                            @foreach($docsCont as $i => $document)
                                @if (!empty($document->url)) <a @if ($i < $count - 1) style="text-decoration: line-through;" @endif href="{{$document->url}}">{{$document->title}}</a> @else <a href="{{route('document.download', [$document->id])}}">{{basename($document->path)}}</a> @endif<br>
                            @endforeach
                        </td>
                    </tr>
                @endforeach
            @endif
        </table>
        <hr>
        <h3 class="text-danger">Інформація про замовника</h3>
        <table  class="table table-striped table-bordered">
            <tr>
                <th>Назва організації</th>
                <td>{{$tender->organization->name}}</td>
            </tr>
            <tr>
                <th>Код ЄДРПОУ</th>
                <td>{{$tender->organization->identifier}}</td>
            </tr>
            <tr>
                <th>Поштова адреса</th>
                <td>{{$tender->organization->getAddress()}}</td>
            </tr>
        </table>

        <hr>
        <h3 class="text-danger">Контактна особа</h3>
        <table  class="table table-striped table-
        bordered">
            @if (!empty($tender->organization->contact_name))
                <tr>
                    <th>Ім'я</th>
                    <td>{{$tender->organization->contact_name}}</td>
                </tr>
            @endif
            @if (!empty($tender->organization->contact_phone))
                <tr>
                    <th>Телефон</th>
                    <td>{{$tender->organization->contact_phone}}</td>
                </tr>
            @endif
            @if (!empty($tender->organization->contact_email))
                <tr>
                    <th>E-mail</th>
                    <td>{{$tender->organization->contact_email}}</td>
                </tr>
            @endif
        </table>

        <hr>


    </div>

@endsection