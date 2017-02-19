<table class="table table-striped">
	<thead>
		<tr>
			<th class="text-center" width="5%"><b>No</b></th>
			<th class="text-center"><b>Kategori</b></th>
			<th class="text-center"><b>Produk</b></th>
			<th class="text-center"><b>Harga</b></th>
			<th class="text-center"><b>Stok</b></th>
			<th class="text-center"><b>Jam Order Maksimal</b></th>
			<th class="text-center"><b>#</b></th>
		</tr>
	</thead>
	<tbody>
	{% for store_item in store_items %}
		<tr>
			<td class="text-right">{{ store_item.rank }}</td>
			<td>{{ store_item.category }}</td>
			<td>{{ store_item.name }} ({{ store_item.stock_unit }})</td>
			<td>Rp. {{ number_format(store_item.price, 0, ',', '.') }}</td>
			<td class="text-center">{{ store_item.stock }}</td>
			<td class="text-center">{{ store_item.order_closing_hour|default('-') }}</td>
			<td class="text-center">
				{% if store_item.price %}
				<a href="javascript:void(0)" data-user-id="{{ user.id }}" data-id="{{ store_item.product_id }}" class="publish">
				{% endif %}
				<i class="fa fa-eye{% if !store_item.published %}-slash{% endif %} fa-2x"></i>
				{% if store_item.price %}
				</a>
				{% endif %}
				<a href="/admin/store_items/update/{{ store_item.product_id }}/user_id:{{ user.id }}{% if page.current > 1%}/page:{{ page.current }}{% endif %}" title="Update"><i class="fa fa-pencil fa-2x"></i></a>
				<a href="javascript:void(0)" data-user-id="{{ user.id }}" data-id="{{ store_item.product_id }}" class="delete" title="Hapus"><i class="fa fa-trash-o fa-2x"></i></a>
			</td>
		</tr>
	{% elsefor %}
		<tr>
			<td colspan="7"><i>Belum ada produk</i></td>
		</tr>
	{% endfor %}
	</tbody>
</table>
{% if page.total_pages > 1 %}
<div class="weepaging">
	<p>
		<b>Halaman:</b>&nbsp;&nbsp;
		{% for i in pages %}
			{% if i == page.current %}
			<b>{{ i }}</b>
			{% else %}
			<a href="/admin/store_items/index/user_id:{{ user.id }}{% if i > 1 %}/page:{{ i }}{% endif %}">{{ i }}</a>
			{% endif %}
		{% endfor %}
	</p>
</div>
{% endif %}
<script>
	for (let items = document.querySelectorAll('.delete'), i = items.length; i--; ) {
		let item = items[i];
		item.onclick = () => {
			if (confirm('Anda yakin menghapus data ini ?')) {
				let form = document.createElement('form');
				form.method = 'POST',
				form.action = '/admin/store_items/delete/' + item.dataset.id + '/user_id:' + item.dataset.userId{% if page.current > 1%} + '/page:' + {{ page.current }}{% endif %},
				document.body.appendChild(form),
				form.submit()
			}
		}
	}
	for (let items = document.querySelectorAll('.publish'), i = items.length; i--; ) {
		let item = items[i];
		items[i].onclick = () => {
			let form = document.createElement('form');
			form.method = 'POST',
			form.action = '/admin/store_items/update/' + item.dataset.id + '/user_id:' + item.dataset.userId + '/published:1'{% if page.current > 1%} + '/page:' + {{ page.current }}{% endif %},
			document.body.appendChild(form),
			form.submit()
		}
	}
</script>
