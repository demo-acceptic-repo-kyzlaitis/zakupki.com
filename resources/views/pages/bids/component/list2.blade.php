
@if ($award)
        <div class="panel panel-info">
            <div class="panel-heading">@if ($award->status == 'pending') Претендент @elseif($award->status == 'active') Переможець @endif</div>
            <div class="panel-body">
                <table  class="table table-striped table-bordered">
                    <tr>
                        <th>Назва організації</th>
                        <td>{{$bidAward->organization->name}}</td>
                    </tr>
                    <tr>
                        <th>Статус</th>
                        <td>{{$award->status}}</td>
                    </tr>
                    <tr>
                        <th>Контактна особа</th>
                        <td>{{$bidAward->organization->conatct_name}}</td>
                    </tr>
                    <tr>
                        <th>Поштова адреса</th>
                        <td>{{$bidAward->organization->getAddress()}}</td>
                    </tr>
                    <tr>
                        <th>Пропозиція</th>
                        <td><p class="text-success">{{$award->amount}} {{$award->currency->currency_description}} @if ($award->tax_included) (Враховуючи ПДВ) @endif</p></td>
                    </tr>
                    <tr>
                        <th>Документи пропозиції</th>
                        <td>
                            @foreach($bidAward->documents as $document)
                                @if (!empty($document->url)) <a  href="{{$document->url}}">{{$document->title}}</a>  @endif<br>
                            @endforeach
                        </td>
                    </tr>
                    <tr>
                        <th>Документи рішення</th>
                        <td>
                            @foreach($award->documents as $document)
                                @if (empty($document->title)) {{basename($document->path)}} @else <a href="{{$document->url}}">{{$document->title}} </a><br>@endif
                            @endforeach
                            @if ($award->status != 'active')
                            <div>&nbsp;</div>
                            {!! Form::model($bid,['route' => ['bid.upload', $bid->award->id],
                            'method'=>'POST',
                            'enctype'=>'multipart/form-data',
                            'class'=>'form-horizontal',]) !!}
                            <fieldset>




                                <div class="form-group">
                                    <div class="col-lg-12">
                                        <input type="file" name="files[]">

                                    </div>
                                    <div class="col-lg-12" style="margin-top: 10px">
                                        {!! Form::submit(Lang::get('keys.download'),['class'=>'btn btn-info']) !!}
                                    </div>
                                </div>
                            </fieldset>
                            {!! Form::close() !!}
                                @endif
                        </td>
                    </tr>
                </table>
                @if($award->status == 'pending')
                <a href="{{route('bid.confirm', [$award->id])}}" class="btn btn-success"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span>{{Lang::get('keys.confirm')}}</a>
                    <a href="{{route('bid.reject', [$award->id])}}" class="btn btn-danger"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span>{{Lang::get('keys.disqualification')}}</a>
                @elseif ($award->status == 'active')
                    <a href="{{route('bid.reject', [$award->id, 's' => 'cancel'])}}" id="#bid-cancel" class="btn btn-danger"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span>{{Lang::get('keys.cancel_bid')}}</a>
                @endif
            </div>
        </div>

        @endif

        @foreach($tender->bids as $bid)
            <?php if ($bidAward && $bid->id == $bidAward->id) continue; ?>

        @endforeach
    </div>
@endsection