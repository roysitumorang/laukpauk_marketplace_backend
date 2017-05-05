{{ flashSession.output() }}
<form method="POST" action="{{ action }}{% if page.current > 1 %}/page:{{ page.current }}{% endif %}">
	<table class="table table-striped">
		<tr>
			<td>
				<b>Nama :</b>
			</td>
			<td>
				{% if setting.id %}
				<i>{{ setting.name }}</i>
				{% else %}
				<input type="text" name="name" value="{{ setting.name }}">
				{% endif %}
			</td>
		</tr>
		<tr>
			<td>
				<b>Nilai :</b>
			</td>
			<td>
				<textarea name="value" rows="4" cols="50">{{ setting.value }}</textarea>
			</td>
		</tr>
		<tr>
			<td>
			</td>
			<td>
				<button type="submit" class="btn btn-info">SIMPAN</button>
			</td>
		</tr>
	</table>
</form>
{{ partial('partials/list_settings', ['page': page, 'pages': pages, 'settings': settings]) }}