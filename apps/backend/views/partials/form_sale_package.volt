{{ flashSession.output() }}
<form method="POST" action="{{ action }}" enctype="multipart/form-data">
	<table class="table table-striped">
		<tr>
			<td>
				<b>Nama :</b>
				<input type="text" name="name" value="{{ sale_package.name }}">
			</td>
		</tr>
		<tr>
			<td>
				<b>Harga :</b>
				<input type="text" name="price" value="{{ sale_package.price }}">
			</td>
		</tr>
		<tr>
			<td>
				<b>Gambar :</b>
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
			<th>#</th>
		</tr>
		{% for product in products %}
		<tr>
			<td>{{ product.rank }}</td>
			<td>{{ product.name }} ({{ product.stock_unit }})</td>
			<td>Rp. {{ product.price | number_format }}</td>
			<td><input type="checkbox" name="user_product_ids[]" value="{{ product.id }}"{% if in_array(product.id, user_product_ids) %} checked{% endif %}></td>
		</tr>
		{% endfor %}
		<tr>
			<td></td>
			<td></td>
			<td></td>
			<td><button type="submit" class="btn btn-primary">SIMPAN</button></td>
		</tr>
	</table>
</form>