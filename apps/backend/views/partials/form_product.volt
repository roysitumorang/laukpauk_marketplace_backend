<form method="POST" action="{{ action }}" enctype="multipart/form-data">
	<table class="table table-striped">
		<tr>
			<td colspan="3">
				<b><font color="#000099">Nama Produk</font></b>
				<br>
				<input type="text" name="code" value="{{ product.code }}" placeholder="Kode Produk" size="30" class="form form-control form-30">
				<input type="text" name="name" value="{{ product.name }}" placeholder="Nama Produk" size="30" class="form form-control form-60">
			</td>
			<td>
				<b><font color="#000099">Stok Produk</font></b>
				<br>
				<input type="text" name="stock" value="{{ product.stock }}" placeholder="Stok Produk" size="10" class="form form-control">
			</td>
		</tr>
		<tr>
			<td bgcolor="#CCCC33">
				<b><font color="#000099">Category</font></b>
				<br>
				<select name="product_category_id" class="form form-control">
				{% for category in categories %}
					<option value="{{ category.id }}"{% if category.id == product.product_category_id %} selected{% endif %}>{% if category.parent_id %}--{% endif %}{{ category.name }} ({{ category.total_products }})</option>
				{% endfor %}
				</select>
			</td>
			<td bgcolor="#cce5ff">
				<b><font color="#000099">Brand</font></b>
				<br>
				<select name="brand_id" class="form form-control">
				{% for brand in brands %}
					<option value="{{ brand.id }}"{% if brand.id == product.brand_id %} selected{% endif %}>{{ brand.name }}</option>
				{% endfor %}
				</select>
			</td>
			<td bgcolor="#CCFFCC">
				<b><font color="#000099">Harga Produk (Rp)</font></b>
				<br>
				<input type="text" name="price" value="{{ product.price }}" placeholder="Harga Produk" size="20" class="form form-control">
			</td>
			<td bgcolor="#99CC33">
				<b><font color="#000099">Berat Produk (gram)</font></b>
				<br>
				<input type="text" name="weight" value="{{ product.weight }}" placeholder="Berat Produk" size="10" class="form form-control">
			</td>
		</tr>
		<tr>
			<td colspan="4">
				<b><font color="#000099">Deskripsi Produk:</font></b><br>
				<textarea name="description" id="description" class="summernote" data-plugin-summernote data-plugin-options="{'height':180,'codemirror':{'theme':'ambiance'}}">{{ product.description }}</textarea>
			</td>
		</tr>
		<tr>
			<td colspan="4">
				<b><font color="#000099">Gambar Produk (1)</font></b>
				<br>
				<input type="hidden" name="product_pictures[0][id]" value="{{ pictures[0].id }}">
				<input type="hidden" name="product_pictures[0][position]" value="{{ pictures[0].position }}">
				<input type="file" name="product_pictures[0]" size="50" class="form form-control form-40">
				{% if pictures[0] %}
				<br>
				<a class="image-popup-no-margins" href="/assets/images/{{ pictures[0].name }}"><img src="/assets/images/{{ pictures[0].thumbnail }}" border="0"></a>
				<br>
				<a href="javascript:void(0)" class="delete" data-product-id="{{ product.id }}" data-id="{{ pictures[0].id }}"><i class="fa fa-trash-o fa-2x"></i></a>
				<br>
				{% endif %}
				Besar file gambar harus di bawah 200 Kb
			</td>
		</tr>
	</table>
	<p style="margin-left:5px">
		<i class="fa fa-plus-square"></i>&nbsp;<a href="javascript:void(0)" onclick="var extra_pictures=document.getElementById('extra_pictures');extra_pictures.setAttribute('style','display:'+(extra_pictures.style.display==='none'?'block':'none'))">Gambar Tambahan</a>
	</p>
	<div id="extra_pictures" style="display:none">
		<table class="table table-striped">
			{% for i in 1..4 %}
			<tr>
				<td>
					<b><font color="#000099">Gambar Produk ({{ i + 1 }})</font></b>
					<br>
					<input type="hidden" name="product_pictures[{{ i }}][id]" value="{{ pictures[i].id }}">
					<input type="hidden" name="product_pictures[{{ i }}][position]" value="{{ pictures[i].position }}">
					<input type="file" name="product_pictures[{{ i }}]" size="50" class="form form-control form-40">
					{% if pictures[i].id %}
					<br>
					<a class="image-popup-no-margins" href="/assets/images/{{ pictures[i].name }}"><img src="/assets/images/{{ pictures[i].thumbnail }}" border="0"></a>
					<br>
					<a href="javascript:void(0)" class="delete" data-product-id="{{ product.id }}" data-id="{{ pictures[i].id }}"><i class="fa fa-trash-o fa-2x"></i></a>
					<br>
					{% endif %}
					Besar file gambar harus di bawah 200 Kb
				</td>
			</tr>
			{% endfor %}
		</table>
	</div>
	<p style="margin-left:5px">
		<input type="checkbox" name="save_variants" onclick="document.getElementById('variants').setAttribute('style','display:'+(this.checked?'block':'none'))">&nbsp;Varian Produk
	</p>
	<div id="variants" style="display:none">
		<table class="table table-striped">
			<thead>
				<tr>
					<th>Parameter</th>
					<th>Stok</th>
					<th>Tambahan Harga (Rp)</th>
					<th>Status</th>
				</tr>
			</thead>
			<tbody>
			{% for i in 0..4 %}
				<tr>
					<td>
						<select name="product_variants[{{ i }}][parameter]" class="form form-control form-30">
						{% for parameter in product_variant_parameters %}
							<option value="{{ parameter }}"{% if parameter == product_variants[i].parameter %} selected{% endif %}>{{ parameter }}</option>
						{% endfor %}
						</select>
						<input type="text" name="product_variants[{{ i }}][value]" value="{{ product_variants[i].value }}" class="form form-control form-60" size="30">
					</td>
					<td><input type="text" name="product_variants[{{ i }}][stock]" value="{{ product_variants[i].stock }}" class="form form-control" size="20"></td>
					<td><input type="text" name="product_variants[{{ i }}][extra_price]" value="{{ product_variants[i].extra_price }}" class="form form-control" placeholder="Max: Rp. 99.999,-" size="20"></td>
					<td>
						<select name="product_variants[{{ i }}][published]" class="form form-control">
						{% for i, status in product_variant_status %}
							<option value="{{ i }}"{% if i == product_variants[i].published %} selected{% endif %}>{{ status }}</option>
						{% endfor %}
						</select>
					</td>
				</tr>
			{% endfor %}
			</tbody>
		</table>
		<i>Kosongkan "Tipe Produk" jika anda ingin menghapus tipe produk tersebut</i><br><br>
	</div>
	<p style="margin-left:5px">
		<input type="checkbox" name="save_dimensions" onclick="document.getElementById('dimensions').setAttribute('style','display:'+(this.checked?'block':'none'))">&nbsp;Dimensi Produk
	</p>
	<div id="dimensions" style="display:none">
		<table class="table table-striped">
			<thead>
				<tr>
					<th>Dimensi Produk</th>
					<th>Ukuran Dimensi</th>
					<th>Satuan</th>
				</tr>
			</thead>
			<tbody>
			{% for i in 0..4 %}
				<tr>
					<td><input type="text" name="product_dimensions[{{ i }}][parameter]" value="{{ product_dimensions[i].parameter }}" class="form form-control" size="30"></td>
					<td><input type="text" name="product_dimensions[{{ i }}][size]" value="{{ product_dimensions[i].size }}" class="form form-control" size="20"></td>
					<td>
						<select name="product_dimensions[{{ i }}][stock_keeping_unit]" class="form form-control">
						{% for stok_keeping_unit in stock_keeping_units %}
							<option value="{{ stock_keeping_unit }}"{% if stock_keeping_unit == product_dimensions[i].stock_keeping_unit %} selected{% endif %}>{{ stock_keeping_unit }}</option>
						{% endfor %}
						</select>
					</td>
				</tr>
			{% endfor %}
			</tbody>
		</table>
	</div>
	<p style="margin-left:5px">
		<i class="fa fa-plus-square"></i>&nbsp;<a href="javascript:void(0)" onclick="var extra_form=document.getElementById('extra_form');extra_form.setAttribute('style','display:'+(extra_form.style.display==='none'?'block':'none'))">Form Tambahan</a>
	</p>
	<div id="extra_form" style="display:none">
		<table class="table table-striped">
			<tr>
				<td>
					<b><font color="#000099">Permalink</font></b>
					<br>
					<input type="text" name="new_permalink" value="{{ product.new_permalink }}" class="form form-control form-50" size="60"><br>
					<i>Kosongkan untuk mengisi permalink secara otomatis</i>
				</td>
			</tr>
			<tr>
				<td>
					<b><font color="#000099">Produk Ditampilkan ?</font></b>
					<br>
					<select name="published" class="form form-control form-40">
						<option value="1"{% if product.published %} selected{% endif %}>YA</option>
						<option value="0"{% if !product.published %} selected{% endif %}>TIDAK</option>
					</select>
					<br>
					<i>Pilih "YA" jika anda ingin menampilkan jenis produk ini atau "TIDAK" jika anda tidak ingin menampilkan jenis produk ini</i>
				</td>
			</tr>
			<tr>
				<td>
					<b><font color="#000099">Status Produk</font></b>
					<br>
					<select name="status" class="form form-control form-40">
						<option value="1"{% if product.status %} selected{% endif %}>Tersedia</option>
						<option value="0"{% if !product.status %} selected{% endif %}>Call Only</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>
					<b><font color="#000099">Poin Beli</font></b>
					<br>
					<input type="text" name="buy_point" value="{{ product.but_point }}" class="form form-control form-20" size="5">
				</td>
			</tr>
			<tr>
				<td>
					<b><font color="#000099">Poin Affiliasi</font></b>
					<br>
					<input type="text" name="affiliate_point" value="{{ product.affiliate_point }}" class="form form-control form-20" size="5">
				</td>
			</tr>
			<tr>
				<td>
					<b><font color="#000099">Berlaku Untuk Daerah</font></b><br>
					{% if product.id %}
					<input type="text" name="imploded_product_cities" value="{{ imploded_product_cities }}" data-role="tagsinput" data-tag-class="label label-primary" class="form form-control form-40">
					{% endif %}
					<select name="product_cities[]" multiple data-plugin-selectTwo class="form-control form-500 populate">
					{% for city in cities %}
						<option value="{{ city.id }}">{{ city.type }} {{ city.name }}</option>
					{% endfor %}
					</select>
					<i>Kosongkan jika discount berlaku ke semua daerah</i>
				</td>
			</tr>
			<tr>
				<td>
					<b><font color="#000099">Meta Title</font></b>
					<br>
					<input type="text" name="meta_title" value="{{ product.meta_title }}" size="70" class="form form-control form-50"><br>
					<i>Maximum 60 characters</i>
				</td>
			</tr>
			<tr>
				<td>
					<b><font color="#000099">Meta Description</font></b>
					<br>
					<textarea name="meta_desc" cols="70" rows="5" class="form form-control form-50">{{ product.meta_desc }}</textarea><br>
					<i>Maximum 160 characters</i>
				</td>
			</tr>
			<tr>
				<td>
					<b><font color="#000099">Meta Keywords</font></b>
					<br>
					<input type="text" name="meta_keyword" value="{{ product.meta_keyword }}" class="form form-control form-50" size="70">
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
<script>
	for (var items = document.querySelectorAll('.delete'), i = items.length; i--; ) {
		items[i].onclick = function() {
			if (!confirm('Anda yakin menghapus gambar ini ?')) {
				return !1
			}
			var form = document.createElement('form');
			form.method = 'POST',
			form.action = '/admin/products/update/' + this.dataset.productId + '/delete_picture:' + this.dataset.id,
			document.body.appendChild(form),
			form.submit()
		}
	}
</script>