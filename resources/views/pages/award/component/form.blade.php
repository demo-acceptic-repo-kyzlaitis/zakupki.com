<?php if (isset($status) && $status == 'reject')
    $route = 'bid.reject';
elseif (isset($status) && $status == 'confirm')
    $route = 'bid.confirm';
else
    $route = 'award.update'; ?>
{!! Form::model($award,['route' => [$route, $award->id],
'method'=>'PUT',
'enctype'=>'multipart/form-data',
'class'=>'form-horizontal',]) !!}
<fieldset>

<?php $organization = ($award->bid) ? $award->bid->organization : $award->organization?>

    <h4>{{$organization->name}} </h4>
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
        <td>{{$organization->conatct_name}}</td>
    </tr>
    <tr>
        <th>Поштова адреса:</th>
        <td>{{$organization->getAddress()}}</td>
    </tr>
    <tr>
        <th>Запропонована ціна:</th>
        <td><p class="text-success">{{$award->amount}}  @if ($award->tax_included) (Враховуючи ПДВ) @endif</p></td>
    </tr>
    @if ($award->bid && $award->bid->values()->count())
        <tr>
            <th>Нецінові показники:</th>
            <td>
                <table class="clean-table">
                    @foreach($award->bid->values as $bidValue)
                        <tr>
                            <th>{{$bidValue->feature->title}}</th>
                            <td>{{$bidValue->title}} ({{$bidValue->value}}%)</td>
                        </tr>
                    @endforeach
                </table>
            </td>
        </tr>
    @endif
    @if ($award->bid && $award->bid->documents()->count())
        <tr>
            <th>Документи пропозиції</th>
            <td>@include('share.component.document-list', ['entity' => $award->bid, 'size' => 'file-icon-sm'])</td>
        </tr>
    @endif
    <tr>
        <td colspan="2">
            <hr>
        </td>
    </tr>

    @if ($award->documents()->count() || (!isset($status)))
        <tr>
            <th>Документи рішення</th>
            <td>
                @if ($award->documents()->count())
                @include('share.component.document-list', ['entity' => $award, 'size' => 'file-icon-sm'])
                @endif
                @if(! isset($status))
                {{--section uploading file--}}
                @include('share.component.add-file-component',['documentTypes' => [], 'index' => '1', 'namespace' => "award-$index", 'inputName' => "award[$index]"])
                {{--section uploading file--}}
                @endif
            </td>
        </tr>
    @endif
</table>

    @if(isset($status) && $status == 'reject')

        <div class="form-group">
            <label for="unsuccessful_title_{{$award->id}}" class="col-md-4 control-label">Причина відхилення</label>
            <div class="col-md-4">
                {!! Form::select("unsuccessful_title[]", $groundsForRejections['titles'], json_decode($award->unsuccessful_title),  ['class' => 'form-control bid_unsuccessful_titles', 'id' => 'unsuccessful_title_' . $award->id, 'multiple']) !!}
            </div>
        </div>
        <div class="form-group">
            <label for="unsuccessful_description_{{$award->id}}" class="col-md-4 control-label">Підстава відхилення</label>

            <div class="col-md-4">
                {!! Form::textarea("unsuccessful_description", $award->unsuccessful_description, ['class' => 'form-control unsuccessful_description', 'id' => 'unsuccessful_description_' . $award->id])  !!}
            </div>
        </div>

        <div class="form-group">
            <div class="col-lg-12">
                {!! Form::submit(Lang::get('keys.cancel'),['class'=>'btn btn-danger center-block']) !!}
            </div>
        </div>

        <script>
            var grounds = $.parseJSON('<?php echo json_encode($groundsForRejections['descriptions']);?>'),
                    groundsForRejection = [];
            $.each(grounds, function (key, val) {
                groundsForRejection[key] = val;
            });
        </script>

    @elseif(isset($status) && $status == 'confirm')

        <div class="form-group">
            <label for="qualified{{$award->id}}" class="col-md-4 control-label">Відповідає кваліфікаційним
                критеріям, встановленим замовником в тендерній документації</label>

            <div class="col-md-4">
                @if($award->qualified)
                    {!! Form::checkbox("qualified", null, $award->qualified, ['class' => 'bid_checkbox', 'id' => 'qualified' . $award->id])  !!}
                @else
                    {!! Form::checkbox("qualified", null, $award->qualified, ['class' => 'bid_checkbox', 'id' => 'qualified' . $award->id])  !!}
                @endif
            </div>
        </div>
        <div class="form-group">
            <label for="eligible{{$award->id}}" class="col-md-4 control-label">Відсутні підстави для
                відмови в участі згідно ст. 17 Закону України ”Про Публічні закупівлі”</label>

            <div class="col-md-4">
                @if($award->eligible)
                    {!! Form::checkbox("eligible", null, $award->eligible, ['class' => 'bid_checkbox', 'id' => 'eligible' . $award->id])  !!}
                @else
                    {!! Form::checkbox("eligible", null, $award->eligible, ['class' => 'bid_checkbox', 'id' => 'eligible' . $award->id])  !!}
                @endif
            </div>
        </div>

        <?php
            $hasDocs = $award->documents()->where('format', '!=', 'application/pkcs7-signature')->count();
        ?>
        @if ($award->tender->procedureType->procurement_method_type != 'reporting' && $hasDocs)
        <div class="form-group">
            <div class="col-lg-12">
                {!! Form::submit(Lang::get('keys.declare_winner'),['class'=>'btn btn-success center-block']) !!}
            </div>
        </div>
        @endif

    @else

        <hr>
    <div class="form-group">
        <div class="col-lg-12 text-center">
            <input type="hidden" class="countFiles" value="0" />
            {!! Form::submit(Lang::get('keys.save'),['class'=>'btn btn-info']) !!}
        </div>
    </div>

    @endif

</fieldset>
{!! Form::close() !!}