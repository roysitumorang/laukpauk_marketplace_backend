{{ flashSession.output() }}
<i style="float:right"><font color="red"><b>*</b> harus diisi</font></i>
<form method="POST" action="{{ action }}">
	<table class="table table-striped">
		<tr>
			<td>
				<b><font color="#000099">Kategori</font> <font color="red">*</font></b>
				<br>
				<select name="product_category_id">
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
				<input type="submit" value="SIMPAN" class="btn btn-info">&nbsp;
				<input type="button" value="BATAL" class="btn btn-warning" onclick="location.href='/admin/products'">
			</td>
		</tr>
	</table>
</form>
