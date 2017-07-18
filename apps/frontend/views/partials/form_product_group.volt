{{ flashSession.output() }}
<form method="POST" action="{{ action }}">
	<table class="table table-striped">
		<tr>
			<td><strong>Nama:</strong></td>
			<td><input type="text" name="name" value="{{ product_group.name }}" size="50" placeholder="Nama"></td>
		</tr>
		<tr>
			<td><strong>Tampilkan:</strong></td>
			<td>
				<input type="radio" name="published" value="1"{% if product_group.published %} checked{% endif %}> Ya
				<input type="radio" name="published" value="0"{% if !product_group.published %} checked{% endif %}> Tidak
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<a type="button" href="/product_groups" class="btn btn-default"><i class="fa fa-chevron-left"></i> Kembali</a>
				<button type="submit" class="btn btn-primary">SIMPAN</button>
			</td>
		</tr>
	</table>
</form>