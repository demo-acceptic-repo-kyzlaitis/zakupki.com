<!-- Modal -->
<div class="modal fade" id="dkpp-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel"> D.K.P.P.</h4>
            </div>
            <div class="modal-body">
                <div class="input-group input-group-lg">
                    <span class="input-group-addon helper" title-data="Пошук">
                        <span class="glyphicon glyphicon-search" aria-hidden="true"></span>
                    </span>
                    <input type="text" class="form-control" placeholder="what would you like to find?"
                           aria-describedby="sizing-addon1">
                </div>
                @if(!empty($codes))
                    <ul class="list-unstyled dkpp-list-container">
                        @foreach($codes as $code)
                            <li class="tree_dkpp" data-id="{{$code->id}}">
                                <input type="radio" name="dkpp" value="{{$code->id}}">
                                {{$code->description}}
                            </li>
                        @endforeach
                    </ul>
                    {!! $codes->render() !!}
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary">SAVE</button>
            </div>
        </div>
    </div>
</div>