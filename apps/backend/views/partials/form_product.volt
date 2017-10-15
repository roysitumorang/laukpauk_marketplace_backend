{{ flashSession.output() }}
<i style="float:right"><font color="red"><b>*</b> harus diisi</font></i>
{{ form(action, 'enctype': 'multipart/form-data') }}
	<table class="table table-striped">
		<tr>
			<td>
				<b><font color="#000099">Kategori</font> <font color="red">*</font></b>
				<br>
				{{ select('product_category_id', categories, 'value': product.product_category_id) }}
			</td>
		</tr>
		<tr>
			<td>
				<b><font color="#000099">Nama Produk</font> <font color="red">*</font></b>
				<br>
				{{ text_field('name', 'value': product.name, 'size': 30, 'placeholder': 'Nama') }}
			</td>
		</tr>
		<tr>
			<td>
				<b><font color="#000099">Satuan Produk</font> <font color="red">*</font></b>
				<br>
				{{ text_field('stock_unit', 'value': product.stock_unit, 'size': 30, 'placeholder': 'Satuan produk') }}
			</td>
		</tr>
		<tr>
			<td>
				<b><font color="#000099">Deskripsi Produk</font></b><br>
				{{ text_area('description', 'value': product.description, 'class': 'summernote', 'data-plugin-summernote': true, 'data-plugin-options': "{'height':180,'codemirror':{'theme':'ambiance'}}", 'placeholder': 'Deskripsi produk') }}
			</td>
		</tr>
		<tr>
			<td>
				<b><font color="#000099">Gambar</font></b>
				<br>
				{{ file_field('new_picture') }}
				{% if product.id and product.picture %}
					<img src="/assets/image/{{ product.picture }}">
					<a href="javascript:void(0)" class="delete" data-id="{{ product.id }}">
						<i class="fa fa-trash-o fa-2x"></i>
					</a>
					<script>
						document.querySelector('.delete').addEventListener('click', event => {
							if (event.preventDefault(), confirm('Anda yakin menghapus gambar ini ?')) {
								let form = document.createElement('form');
								form.method = 'POST',
								form.action = '/admin/products/' + event.target.parentNode.dataset.id + '/delete_picture?next={{ next }}',
								document.body.appendChild(form),
								form.submit()
							}
						}, false)
					</script>
				{% endif %}
			</td>
		</tr>
		<tr>
			<td>
				<b><font color="#000099">Produk Ditampilkan ?</font></b>
				<br>
				{{ radio_field('published', 'value': 1, 'checked': product.published ? true : null, 'id': 'published_' ~ 1) }} Ya&nbsp;&nbsp;
				{{ radio_field('published', 'value': 0, 'checked': product.published ? null : true, 'id': 'published_' ~ 0) }} Tidak
				<br>
				<i>Pilih "Ya" jika Anda ingin menampilkan jenis produk ini atau "Tidak" jika Anda tidak ingin menampilkan jenis produk ini</i>
			</td>
		</tr>
		<tr>
			<td>
				<button type="submit" class="btn btn-primary">SIMPAN</button>&nbsp;
				<a type="button" href="/admin/products" class="btn btn-warning">BATAL</a>
			</td>
		</tr>
	</table>
{{ endForm() }}