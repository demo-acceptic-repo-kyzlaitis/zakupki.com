<h4>
    {{$historyName}}
</h4>

<table class="table table-hover">
	<thead>
		<tr>
			@foreach($tableHeadings as $tableHead)
				<th>{{$tableHead}}</th>
			@endforeach
		</tr>
	</thead>
	<tbody>
        @foreach($history as $historicalEntity)
            <tr>
                <td>{{$historicalEntity->field_value}}</td>
                <td>{{$historicalEntity->created_at}}</td>
            </tr>
        @endforeach
	</tbody>
</table>