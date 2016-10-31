{{ flashSession.output() }}
<form method="POST" action="{{ action }}" enctype="multipart/form-data">
	<table class="table table-striped">
		<tbody>
			<tr>
				<td width="130"><b>Nama Brand</b></td>
				<td><input type="text" name="name" value="{{ brand.name }}" size="40" class="form form-control form-60"></td>
			</tr>
			{% if brand.id %}
			<tr>
				<td><b>Permalink</b></td>
				<td>
					<input type="text" name="new_permalink" value="{{ brand.new_permalink }}" class="form form-control form-60" size="40"><br>
					<i>Kosongkan jika Anda ingin system mengisi permalink secara otomatis</i>
				</td>
			</tr>
			{% endif %}
			<tr>
				<td><b>Icon Brand</b></td>
				<td><input type="file" name="picture" size="40" class="form form-control form-40"></td>
			</tr>
			{% if brand.picture %}
			<tr>
				<td colspan="2">
					<img src="/assets/images/{{ brand.thumbnail }}" border="0"><br>
					<a href="javascript:void(0)" class="delete" data-id="{{ brand.id }}"><i class="fa fa-trash-o fa-2x"></i></a>
				<script>
					document.querySelector('.delete').onclick = function() {
						if (!confirm('Anda yakin menghapus gambar ini ?')) {
							return !1
						}
						var form = document.createElement('form');
						form.method = 'POST',
						form.action = '/admin/brands/update/' + this.dataset.id + '/delete_picture:1',
						document.body.appendChild(form),
						form.submit()
					}
				</script>
				</td>
			</tr>
			{% endif %}
			<tr>
				<td><b>Deskripsi</b></td>
				<td><textarea name="description" cols="80" rows="5" class="form form-control">{{ brand.description }}</textarea></td>
			</tr>
			<tr>
				<td><b>Meta Title</b></td>
				<td><input type="text" name="meta_title" class="form form-control form-50" size="40" value="{{ brand.meta_title }}"></td>
			</tr>
			<tr>
				<td><b>Meta Desc</b></td>
				<td><textarea name="meta_desc" cols="60" rows="2" class="form form-control form-60">{{ brand.meta_desc }}</textarea></td>
			</tr>
			<tr>
				<td><b>Meta Keyword</b></td>
				<td><input name="meta_keyword" type="text" class="form form-control form-50" size="40" value="{{ brand.meta_keyword }}"></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td><button type="submit" class="btn btn-info btn-sm">SIMPAN</button></td>
			</tr>
		</tbody>
	</table>
</form>