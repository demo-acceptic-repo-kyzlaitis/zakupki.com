<table  class="table table-striped table-bordered">
    <tr>
        <th>Ідентифікатор закупівлі</th>
        <th>Найменування</th>
        <th>Статус</th>
        <th>Статус пропозиції</th>
        <th>Статус кваліфікації</th>
        <th>Сума</th>
        <th>Дата початку аукціону</th>
        <th>Аукціон</th>
        <th></th>
    </tr>
    @foreach($bids as $bid)
        <?php $tender = $bid->bidable->tender;?>
        <tr>
            <td>{{$tender->tenderID}}</td>
            <td>
                <a href="{{route('tender.show', [$tender->id])}}">{{$tender->title}}</a>
                @if ($bid->bidable->type == 'lot')
                    <br><span style="color: #666; font-size: 10px;">Лот: {{$bid->bidable->title}}</span>
                @endif
            </td>
            <td><span class="label label-{{$tender->statusDesc->style}}">{{$tender->statusDesc->description}}</span></td>
            <td> @if($bid->award) <span
                        class="label label-{{$bid->award->statusDesc->style}}">{{$bid->award->statusDesc->description}}</span> @elseif($bid->deleted_at !== null)
                    <span class="label label-default">Пропозицію відмінено</span> @elseif($bid->statusDesc)
                    <span class="label label-{{$bid->statusDesc->style}}">{{$bid->statusDesc->description}}</span> @else
                    <span class="label label-default">Пропозиція не розглянута</span>  @endif</td>
            <td> @if($bid->qualification && $bid->qualification->statusDesc) <span
                        class="label label-{{$bid->qualification->statusDesc->style}}">{{$bid->qualification->statusDesc->description}}</span> @else
                    <span class="label label-default">Пропозиція не розглянута</span> @endif</td>
            <td>{{$bid->amount}}</td>
            <td>
                @if (!is_null($bid->bidable->auction_start_date)) {{$bid->bidable->auction_start_date}} @else Дата початку аукціону не визначена @endif
            </td>
            <td>@if (!empty($bid->participation_url)) <a href="{{$bid->participation_url}}" class="move-to-auction-page">Перейти до аукціону</a>@endif</td>
            <td>
                @if($bid->award && $bid->award->status == 'unsuccessful')
                    @if ($bid->award->complaint && $bid->award->complaint->organization->id = Auth::user()->organization->id)
                        <a href="{{route('complaint.edit', [$bid->award->complaint->id])}}" class="btn btn-xs btn-danger">{{Lang::get('keys.edit_complaint')}}</a>
                    @else
                        <a href="{{route('claim.create', ['award', $bid->award->id])}}" class="btn btn-xs btn-danger">{{Lang::get('keys.create_claim')}}</a>
                    @endif
                @endif

                @if($bid->qualification && $bid->qualification->status == 'unsuccessful' && $bid->tender->status == 'active.pre-qualification.stand-still')
                    @if ($bid->qualification->complaint && $bid->qualification->complaint->organization->id = Auth::user()->organization->id)
                        <a href="{{route('complaint.edit', [$bid->qualification->complaint->id])}}" class="btn btn-xs btn-danger">{{Lang::get('keys.edit_complaint_prequalification')}}</a>
                    @else
                        <a href="{{route('claim.create', ['qualification', $bid->qualification->id])}}" class="btn btn-xs btn-danger">{{Lang::get('keys.create_complaint_prequalification')}}</a>
                    @endif
                @endif

                @if ($bid->bidable->canBid() && $bid->deleted_at === null)
                    <a href="{{route('bid.edit', [$bid->id])}}" class="btn btn-xs btn-info helper" title-data="{{Lang::get('keys.edit')}}"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></a>
                @endif

                @if ($bid->deleted_at === null)
                    <a href="{{route('bid.show', [$bid->id])}}" class="btn btn-xs btn-info helper" title-data="Перегляд пропозиції">
                        <span class="glyphicon glyphicon-eye-open" aria-hidden="true" ></span>
                    </a>
                @endif
            </td>
        </tr>

    @endforeach
</table>

@if(count($bids) == 0)
    <h3 class="text-center">За вашим запитом не знайдено жодної пропозиції</h3>
@endif