{{ flashSession.output() }}
<form method="POST" action="{{ action }}">
	<table class="table table-striped">
		<tr>
			<td>
				Versi<br>
				<input type="text" name="version" value="{{ release.version }}" size="10" maxlength="10" placeholder="Versi">
			</td>
		</tr>
		<tr>
			<td>
				Tipe User<br>
				{% for user_type in user_types %}
				<input type="radio" name="user_type" value="{{ user_type }}"{% if release.user_type == user_type %} checked{% endif %}> {{ user_type }}&nbsp;&nbsp;
				{% endfor %}
			</td>
		</tr>
		<tr>
			<td>
				Fitur<br>
				<textarea name="features" placeholder="Fitur" cols="70" rows="10">{{ release.features }}</textarea>
			</td>
		</tr>
		<tr>
			<td>
				<a type="button" href="/admin/releases" class="btn btn-default"><i class="fa fa-chevron-left"></i> KEMBALI</a>
				<button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> SIMPAN</button>
			</td>
		</tr>
	</table>
</form>