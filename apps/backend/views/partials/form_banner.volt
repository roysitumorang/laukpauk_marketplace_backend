{{ flashSession.output() }}
<form method="POST" action="{{ action }}" enctype="multipart/form-data">
	<table class="table table-striped">
		<tr>
			<td>
				<b><font color="#000099">Gambar:</font></b>
				<br>
				<input type="file" name="new_file" size="50">
				<br>
				{% if banner.id and banner.file %}
				<a class="image-popup-no-margins" href="/assets/image/{{ banner.file }}"><img src="/assets/image/{{ banner.file }}" border="0" width="500px" height="250px"></a>
				{% endif %}
			</td>
		</tr>
		<tr>
			<td>
				<b><font color="#000099">Status:</font></b>
				<br>
				<input type="radio" name="published" value="1"{% if banner.published %} checked{% endif %}> Tampilkan&nbsp;
				<input type="radio" name="published" value="0"{% if !banner.published %} checked{% endif %}> Sembunyikan
			</td>
		</tr>
		<tr>
			<td>
				<button type="submit" class="btn btn-primary">SIMPAN</button>
				<a type="button" href="/admin/banners" class="btn btn-default">KEMBALI</a>
			</td>
		</tr>
	</table>
</form>