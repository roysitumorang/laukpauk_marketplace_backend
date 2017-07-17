{{ flashSession.output() }}
<form method="POST" action="{{ action }}" enctype="multipart/form-data">
	<table class="table table-striped">
		<tr>
			<td colspan="2" bgcolor="#999999"><b><font color="#FFFFFF">Input Kategori</font></b></td>
		</tr>
		<tr>
			<td width="30%"><b>Nama</b></td>
			<td><input type="text" name="name" value="{{ category.name }}" size="40" placeholder="Nama"></td>
		</tr>
		<tr>
			<td><b>Permalink</b></td>
			<td>
				<input type="text" name="new_permalink" value="{{ category.new_permalink }}" size="40" placeholder="Permalink"><br>
				<i>Kosongkan jika Anda ingin system mengisi permalink secara otomatis</i>
			</td>
		</tr>
		<tr>
			<td><b>Icon</b></td>
			<td><input type="file" name="picture" size="40"></td>
		</tr>
		{% if category.picture %}
		<tr>
			<td>&nbsp;</td>
			<td>
				<a class="image-popup-no-margins" href="/assets/image/{{ category.picture }}">
					<img src="{{ category.thumbnail }}" border="0">
				</a>
				<br>
				<a href="javascript:void(0)" class="delete" data-user-id="{{ user.id }}" data-id="{{ category.id }}">
					<i class="fa fa-trash-o fa-2x"></i>
				</a>
				<script>
					let removal_link = document.querySelector('.delete');
					removal_link.onclick = () => {
						if (confirm('Anda yakin menghapus gambar ini ?')) {
							let form = document.createElement('form');
							form.method = 'POST',
							form.action = '/product_categories/' + removal_link.dataset.id + '/delete_picture',
							document.body.appendChild(form),
							form.submit()
						}
					}
				</script>
			</td>
		</tr>
		{% endif %}
		<tr>
			<td><b>Status</b></td>
			<td>
				<input type="radio" name="published" value="0"{% if !category.published %} checked{% endif %}> Sembunyikan
				<input type="radio" name="published" value="1"{% if category.published %} checked{% endif %}> Tampilkan
			</td>
		</tr>
		<tr>
			<td><b>Deskripsi</b></td>
			<td>
				<textarea name="description" cols="60" rows="5" placeholder="Deskripsi">{{ category.description }}</textarea>
			</td>
		</tr>
		<tr>
			<td><b>Meta Title</b></td>
			<td><input type="text" name="meta_title" value="{{ category.meta_title }}" size="40" placeholder="Meta Title"></td>
		</tr>
		<tr>
			<td><b>Meta Desc</b></td>
			<td>
				<textarea name="meta_desc" cols="60" rows="2" placeholder="Meta Desc">{{ category.meta_desc }}</textarea>
			</td>
		</tr>
		<tr>
			<td><b>Meta Keyword</b></td>
			<td><input type="text" name="meta_keyword" value="{{ category.meta_keyword }}" size="40" placeholder="Meta Desc"></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td><button type="submit" class="btn btn-info">SIMPAN</button></td>
		</tr>
	</table>
</form>