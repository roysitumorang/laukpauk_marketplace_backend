{{ flashSession.output() }}
{{ form(action ~ (pagination.current > 1 ? '/page:' ~ pagination.current : '')) }}
	<table class="table table-striped">
		<tr>
			<td>
				<b>Tipe :</b>
				{{ select_static('type', types, 'value': city.type) }}
				<b>Nama :</b>
				{{ text_field('name', 'value': city.name) }}
				<button type="submit" class="btn btn-primary">SIMPAN</button>
			</td>
		</tr>
	</table>
{{ endForm() }}
{{ partial('partials/list_cities', ['pagination': pagination, 'pages': pages, 'province': province, 'cities': cities]) }}