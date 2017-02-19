{{ flashSession.output() }}
<form method="POST" action="{{ action }}{% if page.current > 1 %}/page:{{ page.current }}{% endif %}">
	<table class="table table-striped">
		<tr>
			<td>
				<b>Nama :</b>
				<input type="text" name="name" value="{{ village.name }}">
				<button type="submit" class="btn btn-info">SIMPAN</button>
			</td>
		</tr>
	</table>
</form>
{{ partial('partials/list_villages', ['page': page, 'pages': pages, 'subdistrict': subdistrict, 'villages': villages]) }}