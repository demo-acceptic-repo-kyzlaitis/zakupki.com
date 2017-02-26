@extends('layouts.admin')

@section('content')
    {{--Editing Section Start--}}
    <section class="registration container">
        <div class="well">
            <legend>Очередя</legend>

            <table>
                <tr>
                    <td>Default</td>
                    <td> - </td>
                    <td>{{(int) $queue['default']}}</td>
                </tr>
                <tr>
                    <td>Tenders</td>
                    <td> - </td>
                    <td>{{(int) $queue['tenders']}}</td>
                </tr>
                <tr>
                    <td>Bids</td>
                    <td> - </td>
                    <td>{{(int) $queue['bids']}}</td>
                </tr>
            </table>
        </div>
    </section>
    {{--Editing Section End--}}
@endsection