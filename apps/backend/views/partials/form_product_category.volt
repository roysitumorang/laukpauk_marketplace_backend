{{ flashSession.output() }}
<form method="POST" action="{{ action }}" enctype="multipart/form-data">
	{% if category.parent_id %}
	<input type="hidden" name="parent_id" value="{{ category.parent_id }}">
	{% endif %}
	<table class="table table-striped">
		<tr>
			<td colspan="2" bgcolor="#999999"><b><font color="#FFFFFF">Input Kategori</font></b></td>
		</tr>
		<tr>
			<td width="30%"><b>Nama</b> {{ error_message_on_name }}</td>
			<td><input type="text" name="name" value="{{ category.name }}" class="form form-control form-60" size="40"></td>
		</tr>
		<tr>
			<td><b>Permalink</b> {{ error_message_on_permalink }}</td>
			<td>
				<input type="text" name="permalink" value="{{ category.permalink }}" class="form form-control form-60" size="40"><br>
				<i>Kosongkan jika Anda ingin system mengisi permalink secara otomatis</i>
			</td>
		</tr>
		<tr>
			<td><b>Icon</b> {{ error_message_on_picture }}</td>
			<td><input type="file" name="picture" class="form form-control form-40" size="40"></td>
		</tr>
		{% if category.picture %}
		<tr>
			<td>&nbsp;</td>
			<td>
				<a class="image-popup-no-margins" href="/assets/images/{{ category.picture }}">
					<img src="/assets/images/{{ category.thumbnail }}" border="0">
				</a>
				<br>
				<a href="javascript:confirm('Anda yakin menghapus gambar ini ?')&&(location.href='/admin/product_categories/delete_picture/{{ category.id }}?keyword={{ keyword }}&page={{ page }}')">
					<i class="fa fa-trash-o fa-2x"></i>
				</a>
			</td>
		</tr>
		{% endif %}
		<tr>
			<td><b>Status</b> {{ error_message_on_published }}</td>
			<td>
				<input type="radio" name="published" value="0"{% if !category.published %} checked{% endif %}> Sembunyikan
				<input type="radio" name="published" value="1"{% if category.published %} checked{% endif %}> Tampilkan
			</td>
		</tr>
		<tr>
			<td><b>Deskripsi</b> {{ error_message_on_description }}</td>
			<td>
				<textarea name="description" cols="60" rows="5" class="form form-control form-60">{{ category.description }}</textarea>
			</td>
		</tr>
		<tr>
			<td><b>Meta Title</b> {{ error_message_on_meta_title }}</td>
			<td><input type="text" name="meta_title" value="{{ category.meta_title }}" class="form form-control form-50" size="40"></td>
		</tr>
		<tr>
			<td><b>Meta Desc</b> {{ error_message_on_meta_desc }}</td>
			<td>
				<textarea name="meta_desc" cols="60" rows="2" class="form form-control form-60">{{ category.meta_desc }}</textarea>
			</td>
		</tr>
		<tr>
			<td><b>Meta Keyword</b> {{ error_message_on_meta_keyword }}</td>
			<td><input type="text" name="meta_keyword" value="{{ category.meta_keyword }}" class="form form-control form-50" size="40"></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td><button type="submit" class="btn btn-info">SIMPAN</button></td>
		</tr>
	</table>
</form>