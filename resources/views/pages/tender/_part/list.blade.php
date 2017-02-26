<table  class="table table-striped table-bordered">
    <tr>
        <th>ID в системі Prozorro</th>
        <th>Дата початку аукціону</th>
        <th>Аукціон</th>
        <th>Найменування</th>
        <th>Процедура</th>
        <th>Статус</th>
        <th></th>
    </tr>
    @foreach($tenders as $tender)
        <tr @if ($tender->errors->count()) class="danger" @endif>
            @if ($tender->errors->count())
                <td><span class="glyphicon glyphicon-exclamation-sign"></span> Під час публікації тендеру виникли помилки</td>
            @else
                <td>
                    <?php
                    if(empty($tender->tenderID)){
                        echo '(доступний після публікації)';
                    }else{
                    ?><a href="https://prozorro.gov.ua/tender/{{$tender->tenderID}}/">{{$tender->tenderID}}</a><?php
                    }
                    ?>
                </td>
            @endif
            <td>
                @if(is_null($tender->auction_start_date) && $tender->multilot)
                    <?php $isAuction = false;?>
                    @foreach($tender->lots as $lot)
                        @if(!empty($lot->auction_start_date)){{$lot->auction_start_date}}
                        <br><?php $isAuction = true;?>@endif
                    @endforeach
                    @if(!$isAuction)Дата початку не визначена@endif
                @else
                    {{$tender->auction_start_date}}
                @endif
            </td>
            <td>
                @if($tender->lots)
                    @foreach($tender->lots as $lot)
                        @if(!empty($lot->auction_url))<a href="{{$lot->auction_url}}">Перейти до
                            аукціону</a><br>@endif
                    @endforeach
                @endif
            </td>
            <td><a href="{{route('tender.show', [$tender->id])}}">{{$tender->title}}</a></td>
            <td>{{$tender->procedureType->procedure_name}}</td>
            <td><span class="label label-{{$tender->statusDesc->style}}">{{$tender->statusDesc->description}}</span></td>
            <td>
                @if ($tender->canEdit())
                    <a href="{{route('tender.edit', [$tender->id])}}" class="btn btn-xs btn-info helper" title-data="Редагування"><span  class="glyphicon glyphicon-pencil"aria-hidden="true"></span></a>
                @endif
                @if ($tender->canPublish())
                    @if (Auth::user()->organization->mode == 1 && $tender->mode == 0)
                        <a data-href="{{route('tender.publish', [$tender->id])}}" href="#" data-toggle="modal" data-target="#deletepublish{{$tender->id}}"
                           class="btn btn-xs btn-warning helper" title-data="Публікація"><span  class="glyphicon glyphicon-bullhorn" aria-hidden="true"></span></a>
                        @include('share.component.modal-confirm', ['modalNamespace' => 'publish' . $tender->id, 'modalTitle' => 'Публікація', 'modalMessage' => 'Зверніть увагу, що закупівля буде опублікована в тестовому режимі.'])
                    @else
                        <a href="{{route('tender.publish', [$tender->id])}}" class="btn btn-xs btn-warning helper" title-data="Публікація"><span  class="glyphicon glyphicon-bullhorn" aria-hidden="true"></span></a>
                    @endif
                @endif
                @if ($tender->status == 'draft')
                    <a id="delete-tender-{{$tender->id}}" class="btn btn-xs btn-danger"
                       data-toggle="modal" data-target="#modal-confirm-delete-{{$tender->id}}"><span
                                class="glyphicon glyphicon-trash" aria-hidden="true"></span></a>
                    {!! Form::model($tender, ['route' => ['tender.delete', $tender->id], 'method' => 'DELETE', 'id' => 'id-delete-tender-form-'.$tender->id]) !!}
                    @include('share.component.modal-form-confirm', ['modalId' => 'delete-'.$tender->id, 'modalTitle' => 'Видалення тендеру', 'modalMessage' => 'Ви справді хочете видалити тендер?'])
                    {!! Form::close() !!}
                @elseif($tender->canCancel())
                    <a href="{{route('cancel.create', ['tender', $tender->id])}}" class="btn btn-xs btn-danger helper" title-data="Видалення"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></a>
                @endif

            </td>
        </tr>

    @endforeach
</table>
@if(count($tenders) == 0)
    <h3 class="text-center">За вашим запитом не знайдено жодної закупівлі</h3>
@endif