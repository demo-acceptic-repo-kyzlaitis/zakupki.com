<table class="clean-table">
    <tr>
        <th>Конкретна назва предмета закупівлі</th>
        <td class="item-description">{{$item->description}}</td>
    </tr>
    @if($isAboveThresholdEU)
        <tr>
            <th>Конкретна назва предмета закупівлі (англ.)</th>
            <td class="item-description">{{$item->description_en}}</td>
        </tr>
    @endif
    <tr>
        <th>Кількість</th>
        <td class="item-amount"><span class="data-item-amount">{{$item->quantity}}</span> <span class="data-item-amount-code">{{Lang::choice($item->unit->description, $item->quantity, [], 'ru')}}</span></td>
    </tr>
    @foreach($item->codes as $ci => $code)
        @if($item->codes[$ci]->code != '0')
            <tr class="data-item-classifier">
                <th>Класифікатор <span class="@if ($code->classifier->alias == 'cpv') item-classification.scheme @else item-additionalClassifications.scheme @endif data-item-classifier-scheme" data-item-classifier-scheme="{{$code->classifier->scheme}}">{{$code->classifier->name}}</span>
                </th>
                <td class="item-{{$code->classifier->alias}}"><span class="data-item-classifier-code">{{$item->codes[$ci]->code}}</span> <span class="data-item-classifier-desc">{{$item->codes[$ci]->description}}</span></td>
            </tr>
        @endif
    @endforeach
    @if ($item->delivery_date_start !== null || $item->delivery_date_end !== null)
        <tr>
            <th>Період доставки</th>
            <td class="item-delivery_period">@if ($item->delivery_date_start !== null ) з <span class="data-item-delivery-start">{{$item->delivery_date_start}}</span> @endif @if ($item->delivery_date_end !== null) до <span class="data-item-delivery-end">{{$item->delivery_date_end}}</span> @endif</td>
        </tr>
    @endif
    <tr>
        <th>
            Місце поставки
        </th>
        <td class="item-deliveryAddress"><?php echo $item->getAddress();?></td>
    </tr>
    @if ($item->features->count() > 0)
        <tr>
            <th>
                Нецінові показники
            </th>
            <td>
                <table class="clean-table">
                    @foreach($item->features as $feature)
                        <tr>
                            <td>{{$feature->title}}:</td>
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
            </td>
        </tr>

    @endif
</table>