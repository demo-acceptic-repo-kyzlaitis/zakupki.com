@extends('layouts.index')

@section('content')
    {{--Editing Section Start--}}
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <?php $tender = $entity->tender;?>
                @include('share.component.title')
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                @include('share.component.tabs')
            </div>
        </div>
        <section class="registration container">
            <h4>Пропозиція</h4>
            @if (isset($bid))
                <div class="row">
                    <div class="col-md-12">
                        @include('share.component.signature', ['entity' => $bid])
                    </div>
                </div>
            @endif
            @if($errors->has())
                <div class="alert alert-danger" role="alert">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <h4>{{$bid->organization->name}} @if($bid->award) <span class="label label-{{$bid->award->statusDesc->style}}">{{$bid->award->statusDesc->description}}</span> @endif</h4>

            <table class="clean-table">
                <tr>
                    <th>Назва організації:</th>
                    <td>{{$bid->organization->name}}</td>
                </tr>
                <tr>
                    <th>Код ЄДРПОУ:</th>
                    <td>{{$bid->organization->identifier}}</td>
                </tr>
                <tr>
                    <th>Контактна особа:</th>
                    <td>{{$bid->organization->contact_name}}</td>
                </tr>
                <tr>
                    <th>Email:</th>
                    <td>{{$bid->organization->contact_email}}</td>
                </tr>
                <tr>
                    <th>Телефон:</th>
                    <td>{{$bid->organization->contact_phone}}</td>
                </tr>
                <tr>
                    <th>Поштова адреса:</th>
                    <td>{{$bid->organization->getAddress()}}</td>
                </tr>
                @if ($bid->isOwner() || ($tender->status != 'active.tendering' && $tender->status != 'active.pre-qualification' && $tender->status != 'active.pre-qualification.stand-still'))
                    <tr>
                        <th>Запропонована ціна:</th>
                        <td><p class="text-success">{{$bid->amount}}  @if ($bid->tax_included) (Враховуючи ПДВ) @endif</p></td>
                    </tr>
                @endif
                @if ($bid->subcontracting_details)
                    <tr>
                        <th>Інформація про субпідрядника:</th>
                        <td>{{$bid->subcontracting_details}}</td>
                    </tr>
                @endif
                @if ($bid->values()->count())
                    <tr>
                        <th>Нецінові показники:</th>
                        <td>
                            <table class="clean-table">
                                @foreach($bid->values as $bidValue)
                                    <tr>
                                        <th>{{$bidValue->feature->title}}</th>
                                        <td>{{$bidValue->title}} ({{$bidValue->value}}%)</td>
                                    </tr>
                                @endforeach
                            </table>
                        </td>
                    </tr>
                @endif

                @if($bid->qualification && ($tender->procedureType->procurement_method_type == 'aboveThresholdEU' ||
                            $tender->procedureType->procurement_method_type == 'competitiveDialogueUA' ||
                            $tender->procedureType->procurement_method_type == 'competitiveDialogueEU' ||
                            $tender->procedureType->procurement_method == 'selective'))
                    <tr>
                        <th>Результат кваліфікації:</th>
                        <td>
                            @if($bid->qualification && $bid->qualification->status == 'active')
                                <h4><span class="label label-success">Пропозицію допущено до аукціону</span></h4>
                            @elseif($bid->qualification->status == 'unsuccessful')
                                <h4><span class="label label-danger">Пропозицію не допущено до аукціону</span></h4>
                            @else
                                <h4><span class="label label-warning">Пропозицію не розглянуто</span></h4>
                            @endif
                        </td>
                    </tr>
                    @if($bid->qualification->status == 'unsuccessful')
                        <?php $unsuccessfulTitles = json_decode($bid->qualification->unsuccessful_title);
                        ?>
                        <tr>
                            <th>Причини відхилення:</th>
                            <td>
                                @foreach($unsuccessfulTitles as $title)
                                    {{$groundsForRejections['titles'][$title]}}@if ($title != end($unsuccessfulTitles)), @endif
                                @endforeach
                            </td>
                        </tr>
                        <tr>
                            <th>Детальний опис відхилення:</th>
                            <td>{{$bid->qualification->unsuccessful_description}}</td>
                        </tr>
                    @endif
                @endif

                @if ($bid->documents->count())
                    <tr>
                        <th>Документи пропозиції<br>
                            <a onclick="$(this).attr('disabled','disabled');" href="{{route('bid.docs.download.all', ['id' => $bid->id])}}" class="btn btn-info btn-xs">
                                <span class="glyphicon glyphicon-download" aria-hidden="true"></span>{{Lang::get('keys.download_archive')}}</a></th>
                        <td>@include('share.component.document-list', ['entity' => $bid, 'size' => 'file-icon-sm'])</td>
                    </tr>
                @endif

            </table>
        </section>
        {{--@if (isset($bid) && $bid->documents->count() > 0)--}}
            {{--<div class="row">--}}
                {{--<div class="col-md-12">--}}
                    {{--<h4>Документи</h4>--}}
                    {{--@include('admin.pages.bids.document-list', ['entity' => $bid, 'size' => 'file-icon-sm', 'delete' => true, 'route' => 'bid.docs.destroy'])--}}
                {{--</div>--}}
            {{--</div>--}}
            {{--<hr>--}}
        {{--@else--}}
            {{--<div class="col-md-12 text-center">--}}
                {{--<h4>У вашої пропозиції немає документів</h4>--}}
            {{--</div>--}}
            {{--<hr>--}}
        {{--@endif--}}

        @if(count($history) > 0)
            <div class="row">
                <div class="col-md-12">
                    @include('share.component.history', [
                                'history'       => $history,
                                'tableHeadings' => ['Сума', 'Дата оновлення'],
                                'historyName'   => 'Історія зміни пропозиції',
                             ])
                </div>
            </div>
        @endif
        <hr>
        @if(count($featureHistory))
            <div class="row">
                <div class="col-md-12">
                    @include('share.component.history', [
                                'history'       => $featureHistory,
                                'tableHeadings' => ['Неціновий показник', 'Дата оновлення'],
                                'historyName'   => 'Історія зміни нецінових показників',
                            ])
                </div>
            </div>
        @endif

        {{--Editing Section End--}}
    </div>

@endsection
