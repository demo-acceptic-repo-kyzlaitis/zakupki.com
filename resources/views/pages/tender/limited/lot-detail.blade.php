@if($lot->tender->procedureType->procurement_method_type != 'negotiation.quick' &&
            $lot->tender->procedureType->procurement_method_type != 'negotiation')
    <div class="row">
        <div class="col-md-12">
            <div class="col-md-12">
                @if ($lot->items->count() > 0)
                    <h4>Перелік товарів</h4>
                    <table class="table  ">
                        @include('pages.tender.'.$template.'.item-list')
                    </table>
                @endif
            </div>
        </div>
    </div>
@else
    <div class="row">
        <div class="col-md-12">
            <div style="background: #eee; padding: 10px; margin-bottom: 15px; line-height: 2em;">
                <b>{{$lot->title}}</b>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 col-md-offset-6">
            <table class="clean-table">
                @if ($lot->statusDesc)
                    <tr>
                        <th>Статус:</th>
                        <td><span style="font-size: 100%" class="label label-{{$lot->statusDesc->style}}">{{$lot->statusDesc->description}}</span></td>
                    </tr>
                @endif
                <tr>
                    <th>Очікувана вартість закупівлі:</th>
                    <td>{{number_format($lot->amount, 2, '.', ' ')}} {{$tender->currency->currency_code}} @if ($tender->tax_included) (Враховуючи ПДВ) @endif</td>
                </tr>
                @if ($lot->auction_url != '')
                    @if(Auth::check() && Auth::user()->organization->type === 'supplier' && Auth::user()->organization->bids()->where('bidable_id', $lot->id)->first())
                        <tr>
                            <th>Аукціон:</th>
                            <td><a href="{{Auth::user()->organization->bids()->where('bidable_id', $lot->id)->first()->participation_url }}" class="move-to-auction-page">Перейти до аукціону</a></td>
                        </tr>
                    @else
                        <tr>
                            <th>Аукціон:</th>
                            <td><a href="{{$lot->auction_url}}" class="move-to-auction-page">Перейти до аукціону</a></td>
                        </tr>
                    @endif

                @endif
                @if (!is_null($lot->auction_start_date))
                    <tr>
                        <th>Дата початку аукціону</th>
                        <td>з {{$lot->auction_start_date}} </td>
                    </tr>
                @endif
                <tr>
                    <td></td>
                </tr>
                @if ($procedureType->procurement_method == 'open')
                    <tr>
                        @if($lot->guarantee_amount === null)

                            <th>Вид та розмір забезпечення тендерних пропозицій:</th>
                            <td>Відсутнє</td>
                        @else
                            <th>Вид та розмір забезпечення тендерних пропозицій:</th>
                            <td>Електронна гарантія</td>
                    <tr>
                        <th>Сума гарантійного забезпечення закупівлі:</th>
                        <td>{{$lot->guarantee_amount}}</td>
                    </tr>
                    <tr>
                        <th>Валюта гарантійного забезпечення закупівлі:</th>
                        <td>{{$lot->tender->currency->currency_code}}</td>
                    </tr>
                    @endif
                    </tr>
                @endif
            </table>
        </div>
    </div>

    @if ($lot->features->count() > 0)
        <div class="row">
            <div class="col-md-12">
                <div class="col-md-12">
                    <h4>Критерії оцінки</h4>
                    <hr>
                    <table class="clean-table">
                        @foreach($lot->features as $feature)

                            <tr>
                                <th>{{$feature->title}}:</th>
                                <td>{{$feature->getAmount()}}%</td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
    @endif
    <div class="row">
        <div class="col-md-12">
            <div class="col-md-12">
                @if ($lot->items->count() > 0)
                    <h4>Перелік @if (is_object($procedureType) && $procedureType->procurement_method_type == 'competitiveDialogueUA' || $procedureType->procurement_method_type == 'competitiveDialogueEU' || $procedureType->procurement_method == 'selective') робіт або послуг @else товарів @endif</h4>
                    <table class="table  ">
                        @include('pages.tender.'.$template.'.item-list')
                    </table>
                @endif
            </div>
        </div>
</div>
@endif