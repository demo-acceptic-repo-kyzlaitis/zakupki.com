<?php $organization = $award->bid ? $award->bid->organization : $award->organization; ?>
<h4>{{$organization->name}} <span class="label label-{{$award->statusDesc->style}}">{{$award->statusDesc->description}}</span></h4>
<table class="clean-table">
    <tr>
        <th>Назва організації:</th>
        <td>{{$organization->name}}</td>
    </tr>
    <tr>
        <th>Код ЄДРПОУ:</th>
        <td>{{$organization->identifier}}</td>
    </tr>
    <tr>
        <th>Контактна особа:</th>
        <td>{{$organization->contact_name}}</td>
    </tr>
    <tr>
        <th>Поштова адреса:</th>
        <td>{{$organization->getAddress()}}</td>
    </tr>
    <tr>
        <th>Запропонована ціна:</th>
        <td><p class="text-success">{{$award->amount}} @if ($award->currency) {{$award->currency->currency_description}} @endif @if ($award->tax_included) (Враховуючи ПДВ) @else {{ '(Без ПДВ)' }} @endif</p></td>
    </tr>
    @if (isset($values) && $values()->count())
        <tr>
            <th>Нецінові показники:</th>
            <td>
                <table class="clean-table">
                    @foreach($values as $bidValue)
                        <tr>
                            <th>{{$bidValue->feature->title}}</th>
                            <td>{{$bidValue->title}} ({{$bidValue->value}}%)</td>
                        </tr>
                    @endforeach
                </table>
            </td>
        </tr>
    @endif

    @if (isset($documents) && $documents()->count())
        <tr>
            <th>Документи пропозиції</th>
            <td>@include('share.component.document-list', ['entity' => $award->bid, 'size' => 'file-icon-sm'])</td>
        </tr>
    @endif

    <tr>
        <th>Документи рішення</th>
        <td>
            @if ($award->documents()->count())
                @include('share.component.document-list', ['entity' => $award, 'size' => 'file-icon-sm'])
            @endif
            {{--@if ($award->status == 'pending')--}}
                {{--section uploading file--}}
                {{--@include('share.component.add-file-component',['documentTypes' => [], 'index' => 1, 'namespace' => 'award', 'inputName' => 'award'])--}}
                {{--section uploading file--}}
            {{--@endif--}}
        </td>
    </tr>
    @if($award->unsuccessful_description)
        <tr>
            <th>Причини відхилення</th>
            <td>
                {{$award->unsuccessful_description}}
            </td>
        </tr>
    @endif
    <tr>
        <th></th>
        <td>
            @if (Auth::user()->organization->id == $award->tender->organization->id && $award->status == 'pending')
                <a href="{{route('award.edit', [$award->id])}}" class="btn btn-xm btn-danger"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>{{Lang::get('keys.edit')}}</a>
                <?php
                $hasDocs = $award->documents()->where('format', '!=', 'application/pkcs7-signature')->count();
                ?>
                @if ($award->tender->procedureType->procurement_method_type != 'reporting' && $hasDocs)
                    <a href="{{route('bid.confirm', [$award->id])}}" class="btn btn-xm btn-success"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span>{{Lang::get('keys.winner_confirm')}}</a>
                @endif
            @endif
        </td>
    </tr>


</table>




