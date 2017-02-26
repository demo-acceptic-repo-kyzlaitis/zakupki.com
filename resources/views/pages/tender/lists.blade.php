@extends('layouts.index')

@section('content')

    <div style="padding-top: 80px">
        <div class="container">
            <div class="well">
                <form class="form-horizontal">
                    <div class="form-group">
                        <label for="s" class="col-md-2  control-label">Предмет закупівлі</label>
                        <div class="col-md-10">
                            <input type="text" name="search[s]" value="{{$search['s']}}" class="form-control" placeholder="введіть ключові слова або номер закупівлі, наприклад, трактор або UA-2015-09-23-000047">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-2 control-label">Класифікатор</label>
                        <div class="col-md-3">
                            <select name="search[classifier]" class="form-control classifier-selector">
                                <option value="0">Всі класифікатори</option>
                                @foreach($classifiers as $cid => $classifier)
                                    <option @if ($cid == $search['classifier']) selected @endif value="{{$cid}}">{{$classifier}}</option>
                                @endforeach
                            </select>
                        </div>


                        <div class="col-md-7">
                            <input type="text" name="search[code]" value="{{isset($search['code']) ? $search['code'] : ''}}" placeholder="введіть код або назву коду класифікатора та оберіть з випадаючого списку" autocomplete="off" class="form-control classifier">
                            {!! Form::hidden("search[code_id]", isset($search['code_id']) ? $search['code_id'] : '', ['class' => "form-control", 'placeholder' =>''])  !!}
                        </div>

                    </div>

                    <div class="form-group">

                        <label class="col-md-2 control-label">Статус</label>
                        <div class="col-md-3">
                            <select name="search[status]" class="form-control">
                                <option value="0">Всі статуси</option>
                                @foreach($statuses as $status)
                                    <option @if ($status->id == $search['status']) selected @endif value="{{$status->id}}">{{$status->description}}</option>
                                @endforeach
                            </select>
                        </div>
                        <label class="col-md-1 control-label">Регіон</label>
                        <div class="col-md-4">
                            <select name="search[region]" class="form-control">
                                <option value="0">Всі регіони</option>
                                @foreach($regions as $rid => $region)
                                    <option @if ($rid == $search['region']) selected @endif value="{{$rid}}">{{$region}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <button type="submit" class="btn btn-success btn-block"> <span class="glyphicon glyphicon-search" aria-hidden="true"></span> {{Lang::get('keys.find')}}</button>
                        </div>

                    </div>
                </form>
            </div>

            @if (!Auth::user()->organization || Auth::user()->organization->mode == 0)
                <div class="alert alert-danger" role="alert">В тестовому режимі роботи виводяться тільки тестові закупівлі.</div>
            @endif
            <table class="table table-striped">
                @if($tenders && $tenders->count())
                    @foreach($tenders as $tender)

                        <tr>
                            <td width="50%" style="padding: 20px 10px">
                                {{--<div style="padding: 5px 0"><b><a href="{{route('tender.showByID', ['TenderID' => $tender->tenderID])}}">{{$tender->title}}</a></b></div>--}}
                                <div style="padding: 5px 0"><b><a href="{{route('tender.show', ['id' => $tender->id])}}">{{$tender->title}}</a></b></div>
                                <div style="padding: 5px 0; font-size: 14px; font-weight: bold">{{$tender->procedureTypeDesc()}}</div>
                                <div style="padding: 5px 0; font-size: 14px">{{$tender->description}}</div>

                                <div style="color: gray; padding: 5px 0; font-size: 14px"> @if(!empty($tender->organization)) {{$tender->organization->name}} @endif</div>
                                <div style="padding: 5px 0; color: gray; font-size: 13px">
                                    @if ($tender->type_id == 1)
                                        Період уточнень до <span
                                                style="background-color: #eeefef; color: #343434; margin-right: 5px; padding: 6px 5px; zoom: 1;">{{$tender->enquiry_end_date}}</span>
                                    @endif
                                    Прийом пропозицій до <span style="background-color: #eeefef; color: #343434; margin-right: 5px; padding: 6px 5px; zoom: 1;">{{$tender->tender_end_date}}</span>
                                </div>
                            </td>
                            <td style="padding: 20px 10px">
                                <div style="font-size: 20px; font-weight: bold;">{{number_format($tender->amount, 2, '.', ' ')}} <span style="font-size: 12px; color: gray;">@if(!empty($tender->currency)){{$tender->currency->currency_description}}@endif</span></div>
                            </td>
                            <td style="padding: 20px 10px">
                                <div style="margin-bottom: 10px">№ {{$tender->tenderID}}</div>
                                @if (!empty($tender->statusDesc))
                                    <span class="label label-@if(!empty($tender->statusDesc)){{$tender->statusDesc->style}} @endif">
                                        @if(!empty($tender->statusDesc))
                                            {{$tender->statusDesc->description}}
                                        @endif</span>
                                @else
                                    <span class="label label-default">{{$tender->status}}</span>
                                @endif

                            </td>

                        </tr>
                    @endforeach
                @endif
            </table>
            <div class="text-center">
               @if ($tenders) {!! $tenders->appends(['search' => $search])->render() !!} @endif
            </div>
        </div>
    </div>

@endsection

@section('foot')

@endsection
