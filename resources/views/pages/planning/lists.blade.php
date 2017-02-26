@extends('layouts.index')

@section('content')
    <div style="padding-top: 80px"?>
        <div class="container">
            <div class="well">
                <form class="form-inline">
                    <div class="form-group">
                        <label for="s">Предмет закупівлі</label><br>
                        <input type="text" name="s" value="{{$searchString}}" class="form-control" style="width: 400px" placeholder="наприклад, трактор, UA-2015-09-23-000047">
                    </div>
                    <div class="form-group">
                        <label for="status">Статус</label><br>
                        <select name="status" class="form-control" onchange="this.form.submit()">
                            <option></option>
                            @foreach($statuses as $status)
                                <option @if ($status->status == $searchStatus) selected @endif value="{{$status->status}}">{{$status->description}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group" style="margin: 24px 5px 0 5px">
                        <input type="checkbox" class="form-control @if (Auth::user()->organization->mode == 0) only-confirmed" readonly @endif name="mode" value="1" @if ($searchMode == 1) checked @endif > <label @if (Auth::user()->organization->mode == 0) disabled @endif>Тестові закупівлі</label>
                    </div>
                    <div class="form-group">
                        <label>&nbsp;</label><br>
                        <button type="submit" class="btn btn-primary">{{Lang::get('keys.search')}}</button>
                    </div>
                </form>
            </div>
            <table class="table table-striped">
                <?php
                ?>
                @if($tenders->count())
                    @foreach($tenders as $tender)
                        <tr>
                            <td width="50%" style="padding: 20px 10px">
                                {{--<div style="padding: 5px 0"><b><a href="{{route('tender.showByID', ['TenderID' => $tender->tenderID])}}">{{$tender->title}}</a></b></div>--}}
                                <div style="padding: 5px 0"><b><a href="{{route('tender.show', ['id' => $tender->id])}}">{{$tender->title}}</a></b></div>
                                <div style="padding: 5px 0; font-size: 14px">{{$tender->description}}</div>
                                <div style="color: gray; padding: 5px 0; font-size: 14px"> @if(!empty($tender->organization)) {{$tender->organization->name}} @endif</div>
                                <div style="padding: 5px 0; color: gray; font-size: 13px">
                                    Період уточнень до <span style="background-color: #eeefef; color: #343434; margin-right: 5px; padding: 6px 5px; zoom: 1;">{{$tender->enquiry_end_date}}</span>
                                    Прийом пропозицій до <span style="background-color: #eeefef; color: #343434; margin-right: 5px; padding: 6px 5px; zoom: 1;">{{$tender->tender_end_date}}</span>
                                </div>
                            </td>
                            <td style="padding: 20px 10px">
                                <div style="font-size: 20px; font-weight: bold;">{{$tender->amount}} <span style="font-size: 12px; color: gray;">{{$tender->currency->currency_description}}</span></div>
                            </td>
                            <td style="padding: 20px 10px">
                                <div style="margin-bottom: 10px">№ {{$tender->tenderID}}</div>
                                @if ($tender->statusDesc) <span class="label label-{{$tender->statusDesc->style}}">{{$tender->statusDesc->description}}</span> @else <span class="label label-default">{{$tender->status}}</span> @endif

                            </td>

                        </tr>
                    @endforeach
                @endif
            </table>
            <div class="text-center">
                {!! $tenders->appends(['status' => $searchStatus, 's' => $searchString, 'mode' => $searchMode])->render() !!}
            </div>
        </div>
    </div>

@endsection