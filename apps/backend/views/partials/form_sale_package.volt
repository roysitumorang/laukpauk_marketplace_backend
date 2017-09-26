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
					<script>
						document.querySelector('.delete-picture').addEventListener('click', event => {
							if (event.preventDefault(), confirm('Anda yakin mau menghapus gambar ini ?')) {
								let form = document.createElement('form');
								form.method = 'POST',
								form.action = '/admin/users/' + event.target.dataset.userId + '/sale_packages/' + event.target.dataset.id + '/delete_picture',
								document.body.appendChild(form),
								form.submit()
							}
						}, false)
					</script>
				{% endif %}
			</td>
		</tr>
	</table>
	<table class="table table-striped">
		<tr>
			<th>No</th>
			<th>Nama</th>
			<th>Harga</th>
			<th>Quantity</th>
			<th>#</th>
		</tr>
		{% for product in products %}
		<tr>
			<td>{{ product.rank }}</td>
			<td>{{ product.name }} ({{ product.stock_unit }})</td>
			<td>Rp. {{ product.price | number_format }}</td>
			<td>
				<input type="hidden" name="products[{{ product.user_product_id }}][id]" value="{{ product.id }}" data-id="{{ product.user_product_id }}"{% if !user_product_ids.contains(product.user_product_id) %} disabled{% endif %}>
				<select name="products[{{ product.user_product_id }}][quantity]" data-id="{{ product.user_product_id }}"{% if !user_product_ids.contains(product.user_product_id) %} disabled{% endif %}>
				{% for quantity in quantities %}
					<option value="{{ quantity }}"{% if quantity == product.quantity %} selected{% endif %}>{{ quantity }}</option>
				{% endfor %}
				</select>
			</td>
			<td><input type="checkbox" name="products[{{ product.user_product_id }}][user_product_id]" value="{{ product.user_product_id }}"{% if user_product_ids.contains(product.user_product_id) %} checked{% endif %}></td>
		</tr>
		{% endfor %}
		<tr>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td><button type="submit" class="btn btn-primary">SIMPAN</button></td>
		</tr>
	</table>
</form>
<script>
	document.querySelectorAll('[type=checkbox]').forEach(item => {
		item.addEventListener('click', event => {
			document.querySelectorAll('[data-id="' + event.target.value + '"]').forEach(target => {
				event.target.checked
				? target.removeAttribute('disabled')
				: target.setAttribute('disabled', '')
			})
		}, false)
	})
</script>