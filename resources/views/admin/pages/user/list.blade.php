@extends('layouts.admin')

@section('content')
        @if($users->count())
            <div class="row">
                <table  class="table table-striped table-bordered">
                    <tr>
                        <th>Id</th>
                        <th>Email</th>
                        <th>Ім'я</th>
                        <th>Організація</th>
                        <th>Дата</th>
                        <th>Дії</th>
                    </tr>
                    @foreach($users as $user)
                        <tr @if ($user->active == 0) class="danger" @endif>
                            <td>{{$user->id}}</td>
                            <td>{{$user->email}}</td>
                            <td>{{$user->name}}</td>

                            <td><a href="">@if ($user->organization) {{$user->organization->name}} @endif</a></td>
                            <td>{{$user->created_at}}</td>
                            <td>
                                <a href="{{route('admin::user.edit', $user->id)}}" class="btn btn-xs btn-info"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></a>
                            </td>
                        </tr>

                    @endforeach
                </table>
            </div>

            <div class="text-center">
                {!! $users->render() !!}
            </div>
        @endif
@endsection



