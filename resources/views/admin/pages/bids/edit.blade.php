@extends('layouts.admin')

@section('content')
    {{--Editing Section Start--}}
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <?php $tender = $entity->tender;?>
                @include('share.component.title')
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                @include('share.component.tabs')
            </div>
        </div>
        <section class="registration container">
            <h4>Пропозиція</h4>
            @if($errors->has())
                <div class="alert alert-danger" role="alert">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            {!! Form::model($bid,['route' => ['bid.update', $bid->id],
            'method'=>'PUT',
            'enctype'=>'multipart/form-data',
            'class'=>'form-horizontal']) !!}
            <fieldset>


                @include('admin.pages.bids.form')

                <hr>
                <div class="form-group">
                    <div class="col-lg-12">
                        <a class="btn btn-danger" href="{{route('admin::bids.index')}}">{{Lang::get('keys.back')}}</a>
                    </div>
                </div>
            </fieldset>
            <script>

            </script>
        </section>
        {{--Editing Section End--}}
    </div>
@endsection
