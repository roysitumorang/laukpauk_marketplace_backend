{{ flashSession.output() }}
<form method="POST" action="{{ action }}{% if page.current > 1 %}/page:{{ page.current }}{% endif %}">
	<table class="table table-striped">
		<tr>
			<td>
				<b>Tipe :</b>
				<select name="type">
				{% for type in types %}
					<option value="{{ type }}"{% if type == city.type %} selected{% endif %}>{{ type }}</option>
				{% endfor %}
				</select>
				<b>Nama :</b>
				<input type="text" name="name" value="{{ city.name }}">
				<button type="submit" class="btn btn-info">SIMPAN</button>
			</td>
		</tr>
	</table>
</form>
{{ partial('partials/list_cities', ['page': page, 'pages': pages, 'province': province, 'cities': cities]) }}