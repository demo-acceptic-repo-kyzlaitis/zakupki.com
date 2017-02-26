@extends('layouts.index')

@section('content')
    {{--Editing Section Start--}}

    <section class="registration container">
        <div class="text-right">
            @if ($tender->published_at == '0000-00-00 00:00:00')
                <a href="{{route('tender.publish', [$tender->id])}}" class="btn btn-info">{{Lang::get('keys.publish')}}</a>
            @endif
            @if (strtotime($tender->tender_start_date) > time() && ($tender->status == 'active.enquiries' || $tender->status == 'draft'))
                <a href="{{route('tender.edit', [$tender->id])}}" class="btn btn-info">{{Lang::get('keys.edit')}}</a>
            @endif
                @if ($tender->cbd_id != '' && $tender->status != 'cancelled' && $tender->status != 'complete')
                <a href="{{route('tender.cancel', [$tender->id])}}" class="btn btn-danger">{{Lang::get('keys.cancel')}}</a>
            @endif
        </div>
        <h2>Тендер {{$tender->tenderID}}

            <?php
            $labels = [
                    'draft' => 'default',
                    'active.enquiries' => 'info',
                    'cancelled' => 'default',
                    'active.qualification' => 'warning',
                    'active.tendering' => 'primary',
                    'unsuccessful' => 'danger',
                    'active.awarded' => 'success',
                    'active.auction' => 'warning',
                    'complete' => 'success'
            ];
            ?>
            <span class="label label-{{$labels[$tender->status]}}">{{$tender->statusDesc()->first()->description}}</span>
        </h2>
        <div>&nbsp;</div>
        @include('share.component.tabs')
        <h3>Документи</h3>
        <table  class="table table-striped table-bordered">
            @if (isset($contract))
                @foreach($contract->documents as $document)
                    <tr>
                        <td>@if (empty($document->title)) {{basename($document->path)}} @else <a href="($document->url)}}">{{$document->title}} </a>@endif</td>

                        <td>
                            @if ($contract->status != 'active')
                                <a data-href="{{route('contract.docs.delete', [$document->id])}}" href="#" data-toggle="modal" data-target="#deleteContractDocs" class="fileUpload btn btn-danger btn-xs">
                                    <span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            @endif
        </table>
    </section>
    {{--Editing Section End--}}
@endsection