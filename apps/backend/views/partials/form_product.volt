{{ flashSession.output() }}
<i style="float:right"><font color="red"><b>*</b> harus diisi</font></i>
<form method="POST" action="{{ action }}" enctype="multipart/form-data">
	<table class="table table-striped">
		<tr>
			<td>
				<b><font color="#000099">Merchant</font></b>
				<br>
				{% if product.id %}
					{{ product.user ? product.user.company : '-' }}
				{% else %}
					<select name="user_id" id="user_id">
						<option value=""></option>
						{% for merchant in merchants %}
							<option value="{{ merchant.id }}"{% if merchant.id == product.user_id %} selected{% endif %}>{{ merchant.company }}</option>
						{% endfor %}
					</select>
				{% endif %}
			</td>
		</tr>
		<tr>
			<td>
				<b><font color="#000099">Kategori</font> <font color="red">*</font></b>
				<br>
				<select name="product_category_id" id="category_id">
				{% for category in categories %}
					<option value="{{ category.id }}"{% if category.id == product.category.id %} selected{% endif %}>{% if category.parent_id %}--{% endif %}{{ category.name }} ({{ category.total_products }})</option>
				{% endfor %}
				</select>
			</td>
		</tr>
		<tr>
			<td>
				<b><font color="#000099">Nama Produk</font> <font color="red">*</font></b>
				<br>
				<input type="text" name="name" value="{{ product.name }}" placeholder="Nama Produk" size="30" placeholder="Nama">
			</td>
		</tr>
		<tr>
			<td>
				<b><font color="#000099">Satuan Produk</font> <font color="red">*</font></b>
				<br>
				<input type="text" name="stock_unit" value="{{ product.stock_unit }}" placeholder="Satuan Produk" size="30" placeholder="Satuan produk">
			</td>
		</tr>
		<tr>
			<td>
				<b><font color="#000099">Deskripsi Produk</font></b><br>
				<textarea name="description" id="description" class="summernote" data-plugin-summernote data-plugin-options="{'height':180,'codemirror':{'theme':'ambiance'}}" placeholder="Deskripsi produk">{{ product.description }}</textarea>
			</td>
		</tr>
		<tr>
			<td>
				<b><font color="#000099">Masa Pakai</font> <font color="red">*</font></b>
				<br>
				<select name="lifetime">
					<option value="">-</option>
					{% for lifetime in lifetimes %}
					<option value="{{ lifetime }}"{% if lifetime == product.lifetime  %} selected{% endif %}>{{ lifetime }} hari</options>
					{% endfor %}
				</select>
			</td>
		</tr>
		<tr>
			<td>
				<b><font color="#000099">Gambar</font></b>
				<br>
				<input type="file" name="picture">
				{% if product.id and product.picture %}
				<img src="/assets/image/{{ product.picture }}">
				<a href="javascript:void(0)" class="delete" data-id="{{ product.id }}">
					<i class="fa fa-trash-o fa-2x"></i>
				</a>
				<script>
					let removal_link = document.querySelector('.delete');
					removal_link.onclick = () => {
						if (confirm('Anda yakin menghapus gambar ini ?')) {
							let form = document.createElement('form');
							form.method = 'POST',
							form.action = '/admin/products/' + removal_link.dataset.id + '/delete_picture?next={{ next }}',
							document.body.appendChild(form),
							form.submit()
						}
					}
				</script>
				{% endif %}
			</td>
		</tr>
		<tr>
			<td>
				<b><font color="#000099">Produk Ditampilkan ?</font></b>
				<br>
				<input type="radio" name="published" value="1"{% if product.published %} checked{% endif %}> Ya&nbsp;&nbsp;
				<input type="radio" name="published" value="0"{% if !product.published %} checked{% endif %}> Tidak
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
</form>
{% if !product.id %}
<script>
	let user_id = document.getElementById('user_id'), category_id = document.getElementById('category_id');
	user_id.onchange = () => {
		fetch('/admin/products/categories' + (user_id.value ? '/user_id:' + user_id.value : ''), { credentials: 'include' }).then(response => {
			return response.text()
		}).then(payload => {
			let result = JSON.parse(payload), new_options = '';
			result.forEach(item => {
				new_options += '<option value="' + item.id + '">' + item.name + ' (' + item.total_products +')</option>'
			}),
			category_id.innerHTML = new_options
		})
	}
</script>
{% endif %}