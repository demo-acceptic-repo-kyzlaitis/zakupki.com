@extends('layouts.index')

@section('content')

    <div class="container">
        <div class="row">
            <div class="col-md-12">
                @include('share.component.title')

            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                @include('share.component.buttons')
                @include('share.component.tabs')
            </div>
        </div>
        @if (($tender->procedureType->threshold_type == 'above') && !$tender->signed)
                <div class="alert alert-danger" role="alert">
                    Закупівлю потрібно підписати ЕЦП
                </div>
        @endif
            <?php $awards = $tender->awards()->orderBy('created_at', 'desc')->get(); ?>
            @foreach($awards as $i => $award)
                @if ($award->bid)
                    <div style="background: #eee; padding: 10px; margin-bottom: 15px; line-height: 2.5em;"><b>{{$i + 1}}. {{$award->bid->bidable->title}}</b></div>
                @endif
                @if ((in_array($award->status,['activate','unsuccessfully']))&&(!$award->signed))
                	<div class="alert alert-danger" role="alert">
                    	Рішення потрібно підписати ЕЦП
                	</div>
                @endif
                <div style="float:right">

                    @if(!$tender->hasAnyAcceptedComplaints())
                        @if($award->status == 'pending')
                            <a href="@if (($tender->procedureType->procurement_method == 'open' && $tender->procedureType->procurement_method_type != 'belowThreshold') || $tender->procedureType->procurement_method == 'selective') {{route('bid.confirm.form', [$award->id])}}  @else {{route('bid.confirm', [$award->id])}} @endif"
                               class="btn btn-xm btn-success"><span class="glyphicon glyphicon-ok"
                                                                    aria-hidden="true"></span>{{Lang::get('keys.declare_winner')}}</a>
                            <a href="@if (($tender->procedureType->procurement_method == 'open' && $tender->procedureType->procurement_method_type != 'belowThreshold') || $tender->procedureType->procurement_method == 'selective') {{route('bid.reject.form', [$award->id])}} @else {{route('bid.reject', [$award->id])}} @endif"
                               class="btn btn-xm  btn-danger confirment"><span class="glyphicon glyphicon-remove confirment"
                                                                    aria-hidden="true"></span> {{Lang::get('keys.cancel')}}</a>
                        @elseif ((in_array($award->status,['activate','unsuccessfully']))&&(!$award->signed))
                               <a href="#" class="btn btn-success" data-toggle="modal" data-target="#signature-set" data-id="{{$award->id}}" data-documentname="award">{{Lang::get('keys.sign_ecp')}}</a>
                        @elseif ($award->status == 'active' || ($award->status == 'unsuccessful' && $award->complaint))
                            <a href="{{route('bid.reject', [$award->id, 's' => 'cancel'])}}" class="btn btn-danger"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span>{{Lang::get('keys.cancel_qualification')}}</a>
                        @endif
                    @endif



                </div>
                <div class="row">

                    <div class="col-md-12">
                        @if ($award->status == 'pending') @include('pages.award.component.form', ['index'=>$i]) @else @include('pages.award.component.detail') @endif<div class="col-md-12">

                        </div></div></div>
            @endforeach

    </div>

            <!-- add IIT library -->
    <script type="text/javascript" src="/js/sign/euscpt.js"></script>
    <script type="text/javascript" src="/js/sign/euscpm.js"></script>
    <script type="text/javascript" src="/js/sign/euscp.js" async=""></script>

    <!-- add public script from openprocurement-crypto for prepareObject() -->
    <script type="text/javascript" src="https://cdn.rawgit.com/openprocurement-crypto/common/v.0.0.23/js/index.js"></script>

    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/ladda-bootstrap/0.9.4/spin.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/ladda-bootstrap/0.9.4/ladda.min.js"></script>

    <script type="text/javascript" src="/js/sign/promise.min.js"></script>
    <script type="text/javascript" src="/js/sign/jsondiffpatch.min.js"></script>
    <script type="text/javascript" src="/js/sign/signer.js"></script>

    @include('share.component.modal-ecp')
    
@endsection