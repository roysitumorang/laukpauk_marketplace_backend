{{ flashSession.output() }}
<div class="tabs">
	{{ partial('partials/tabs_product', ['product': product, 'expand': 'product']) }}
	<div class="tab-content">
		<div id="product" class="tab-panel active">
			<form method="POST" action="{{ action }}">
				<table class="table table-striped">
					<tr>
						<td>
							<b><font color="#000099">Kategori</font></b>
							<br>
							<select name="product_category_id" class="form form-control">
							{% for category in categories %}
								<option value="{{ category.id }}"{% if category.id == product.category.id %} selected{% endif %}>{% if category.parent_id %}--{% endif %}{{ category.name }} ({{ category.total_products }})</option>
							{% endfor %}
							</select>
						</td>
					</tr>
					<tr>
						<td>
							<b><font color="#000099">Nama Produk</font></b>
							<br>
							<input type="text" name="name" value="{{ product.name }}" placeholder="Nama Produk" size="30" class="form form-control">
						</td>
					</tr>
					<tr>
						<td>
							<b><font color="#000099">Deskripsi Produk:</font></b><br>
							<textarea name="description" id="description" class="summernote form form-control" data-plugin-summernote data-plugin-options="{'height':180,'codemirror':{'theme':'ambiance'}}">{{ product.description }}</textarea>
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
				</table>
				</div>
				<table class="table table-striped">
					<tr>
						<td>
							<input type="submit" value="SIMPAN" class="btn btn-info">&nbsp;
							<input type="button" value="BATAL" class="btn btn-warning" onclick="location.href='/admin/products'">
						</td>
					</tr>
				</table>
			</form>
		</div>
	</div>
</div>
