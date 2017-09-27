{{ flashSession.output() }}
<form method="POST" action="{{ action }}" enctype="multipart/form-data">
	<table class="table table-striped">
		<tr>
			<td>
				<b>Nama :</b>
			</td>
			<td>
				<input type="text" name="name" value="{{ sale_package.name }}" placeholder="Nama">
			</td>
		</tr>
		<tr>
			<td>
				<b>Harga :</b>
			</td>
			<td>
				<input type="text" name="price" value="{{ sale_package.price }}" placeholder="Harga">
			</td>
		</tr>
		<tr>
			<td>
				<b>Stok :</b>
			</td>
			<td>
				<input type="text" name="stock" value="{{ sale_package.stock }}" placeholder="Stok">
			</td>
		</tr>
		<tr>
			<td>
				<b>Gambar :</b>
			</td>
			<td>
				<input type="file" name="new_picture">
				{% if sale_package.picture %}
					<img src="/assets/image/{{ sale_package.picture | strtr(['.jpg': '300.jpg']) }}">
					<br>
					<a type="button" data-user-id="{{ sale_package.user_id }}" data-id="{{ sale_package.id }}" class="btn btn-danger delete-picture"><i class="fa fa-trash"></i> Hapus Gambar</a>
				{% endif %}
			</td>
		</tr>
		<tr>
			<td></td>
			<td><button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> SIMPAN</button></td>
		</tr>
	</table>
</form>
{% if sale_package.id %}
	{% if !new_products.isEmpty() %}
		<form method="POST" action="/admin/users/{{ user.id }}/sale_packages/{{ sale_package.id }}/products/create">
			<table class="table table-striped">
				<tr>
					<td>
						<b>Produk :</b>
						<br>
						<select name="new_product[user_product_id]">
						{% for product in new_products %}
							<option value="{{ product.user_product_id }}"{% if product.user_product_id == new_product.user_product_id %} selected{% endif %}>{{ product.name }} ({{ product.stock_unit }})</option>
						{% endfor %}
						</select>
					</td>
					<td>
						<b>Quantity :</b>
						<br>
						<select name="new_product[quantity]">
						{% for quantity in quantities %}
							<option value="{{ quantity }}"{% if quantity == new_product.quantity %} selected{% endif %}>{{ quantity }}</option>
						{% endfor %}
						</select>
					</td>
					<td>
						<br>
						<button type="submit" class="btn btn-primary"><i class="fa fa-plus"></i> TAMBAH</button>
					</td>
				</tr>
			</table>
		</form>
	{% endif %}
	<form method="POST" action="/admin/users/{{ user.id }}/sale_packages/{{ sale_package.id }}/products/update">
		<table class="table table-striped">
			<tr>
				<th>No</th>
				<th>Nama</th>
				<th>Harga</th>
				<th>Quantity</th>
				<th>#</th>
			</tr>
			{% for product in existing_products %}
			<tr>
				<td>{{ product.rank }}</td>
				<td>{{ product.name }} ({{ product.stock_unit }})</td>
				<td>Rp. {{ product.price | number_format }}</td>
				<td>
					<select name="product[{{ product.id }}][quantity]">
					{% for quantity in quantities %}
						<option value="{{ quantity }}"{% if quantity == product.quantity %} selected{% endif %}>{{ quantity }}</option>
					{% endfor %}
					</select>
				</td>
				<td><a data-user-id="{{ user.id }}" data-sale-package-id="{{ sale_package.id }}" data-id="{{ product.id }}" class="delete"><i class="fa fa-trash fa-2x"></i></a></td>
			</tr>
			{% endfor %}
			<tr>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td><button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> SIMPAN</button></td>
			</tr>
		</table>
	</form>
	<script>
		{% if sale_package.picture %}
		document.querySelector('.delete-picture').addEventListener('click', event => {
			if (event.preventDefault(), confirm('Anda yakin mau menghapus gambar ini ?')) {
				let form = document.createElement('form');
				form.method = 'POST',
				form.action = '/admin/users/' + event.target.dataset.userId + '/sale_packages/' + event.target.dataset.id + '/delete_picture',
				document.body.appendChild(form),
				form.submit()
			}
		}, false),
		{% endif %}
		document.querySelectorAll('.delete').forEach(item => {
			item.addEventListener('click', event => {
				if (event.preventDefault(), confirm('Anda yakin mau menghapus data ini ?')) {
					let form = document.createElement('form');
					form.method = 'POST',
					form.action = '/admin/users/' + item.dataset.userId + '/sale_packages/' + item.dataset.salePackageId + '/products/' + item.dataset.id + '/delete',
					document.body.appendChild(form),
					form.submit()
				}
			}, false)
		})
	</script>
{% endif %}