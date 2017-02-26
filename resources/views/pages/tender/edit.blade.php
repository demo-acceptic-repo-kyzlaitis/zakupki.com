@extends('layouts.index')

@section('content')


    @if(Session::has('modal_flash_upload_docs'))
        <div class="modal fade" id="upload-doc-reminder">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">Завантаження документів, що стосуються закупівлі.</h4>
                    </div>
                    <div class="modal-body">
                        Шановний користувач, завантажте, будь ласка, всі необхідні документи, що стосуються даної закупівлі, натиснувши "Додати файл" в розділі "Документація".
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">{{Lang::get('keys.next')}}</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->

        <script>
            window.onload = function() {
                $('#upload-doc-reminder').modal('show');
            };
        </script>
    @endif
    {{--Editing Section Start--}}
    <section class="registration container">
        <h2>Редагування закупівлі</h2>
        {{--<input type="hidden" id="isEditable" data-editable="true">--}}
        <div class="row">
            <div class="col-md-1"></div>
            <div class="col-md-9">
                @if($errors->has())
                    <div class="alert alert-danger" role="alert">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <div class="alert alert-danger errors-create" role="alert" hidden>
                        <ul id="errors">
                        </ul>
                    </div>
                @endif
                {!! Form::model($tender,['route' => ['tender.update', $tender->id],
                'method'=>'PUT',
                'enctype'=>'multipart/form-data',
                'class'=>'form-horizontal', 'id' => 'edit-form-submit']) !!}
                    {!! Form::hidden('tender_id', $tender->id) !!}

                    @include('pages.tender.'.$template.'.form')

                    <hr>
                    <div class="form-group">
                        <div class="col-lg-12 text-center">
                            {!! Form::submit(Lang::get('keys.save'),['class'=>'btn btn-lg btn-danger', 'id' => 'submit-edit-btn']) !!}
                        </div>
                    </div>
                </fieldset>
                {!! Form::close() !!}
            </div>
            <div class="col-md-1"></div>
        </div>
    </section>
    {{--Editing Section End--}}
@endsection