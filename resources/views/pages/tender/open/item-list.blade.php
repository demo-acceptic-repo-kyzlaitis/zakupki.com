@foreach($lot->items as $itemIndex => $item)
    <tr>
        <td>

        </td>
        <td>
            <div class="row">
                <div class="col-md-12">
                    <ul class="nav nav-tabs" role="tablist" style="font-size: 85%;">
                        <li role="presentation" class="active"><a href="#lot-{{$index}}-item-{{$itemIndex}}" aria-controls="lot-{{$index}}-item-{{$itemIndex}}" role="tab" data-toggle="tab">Інформація</a></li>
                        <li role="presentation" class="@if ($item->documents->count() == 0) disabled @endif" ><a @if ($item->documents->count() > 0) href="#lotdocs-{{$index}}-item-{{$itemIndex}}" aria-controls="lotdocs-{{$index}}-item-{{$itemIndex}}" role="tab" data-toggle="tab" @endif>Документація товару</a></li>
                        <li role="presentation" class="@if ($item->questions->count() == 0) disabled @endif"><a @if ($item->questions->count() > 0) href="#lotquestions-{{$index}}-item-{{$itemIndex}}" aria-controls="lotquestions-{{$index}}-item-{{$itemIndex}}" role="tab" data-toggle="tab" @endif>Запитання ({{$item->questions->count()}})</a></li>

                        @if ($lot->canQuestion() && (Auth::check() && !$tender->isOwner(Auth::user()->id)) && Auth::user()->organization->type == 'supplier')
                            <a href="{{route('questions.create', ['item', $item->id])}}"
                               class="btn btn-success pull-right">{{Lang::get('keys.create_question')}}</a>
                        @endif
                    </ul>
                </div>
            </div>
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane fade in active" id="lot-{{$index}}-item-{{$itemIndex}}">
                    @include('pages.tender.'.$template.'.item-detail', ['item' => $item])
                </div>
                <div role="tabpanel" class="tab-pane fade" id="lotdocs-{{$index}}-item-{{$itemIndex}}">
                    @include('share.component.document-list', ['entity' => $item, 'size' => 'file-icon-sm'])
                </div>
                <div role="tabpanel" class="tab-pane fade" id="lotquestions-{{$index}}-item-{{$itemIndex}}">
                    @include('pages.question.component.questions-list', ['entity' => $item])
                </div>

            </div>
        </td>
    </tr>
@endforeach