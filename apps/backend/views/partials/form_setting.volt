{{ flashSession.output() }}
<form method="POST" action="{{ action }}{% if page.current > 1 %}/page:{{ page.current }}{% endif %}">
	<table class="table table-striped">
		<tr>
			<td>
				<b>Nama :</b>
				{% if setting.id %}
				<i>{{ setting.name }}</i>
				{% else %}
				<input type="text" name="name" value="{{ setting.name }}">
				{% endif %}
				<b>Nilai :</b>
				<input type="text" name="value" value="{{ setting.value }}">
				<button type="submit" class="btn btn-info">SIMPAN</button>
			</td>
		</tr>
	</table>
</form>
{{ partial('partials/list_settings', ['page': page, 'pages': pages, 'settings': settings]) }}