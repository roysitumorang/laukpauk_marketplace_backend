<div class="tabs">
	{{ partial('partials/tabs_product', ['product': product, 'expand': 'stock_units']) }}
	<div class="tab-content">
		<div id="stock_units" class="tab-panel active">
			{{ flashSession.output() }}
			<form method="POST" action="{{ action }}">
				<table class="table table-striped">
					<tr>
						<td>
							<b><font color="#000099">Nama Satuan :</font></b>
							<input type="text" name="name" value="{{ stock_unit.name }}" placeholder="Nama Satuan" size="30" class="form form-control-50">
							<button type="submit" class="btn btn-info">SIMPAN</button>
						</td>
					</tr>
				</table>
			</form>
			<table class="table table-striped">
				<thead>
					<tr>
						<th width="25"><b>No</b></th>
						<th><b>Satuan</b></th>
						<th><b>#</b></th>
					</tr>
				</thead>
				<tbody>
				{% for stock_unit in stock_units %}
					<tr id="{{ stock_unit.id }}">
						<td class="text-right">{{ stock_unit.rank }}</td>
						<td>{{ stock_unit.name }}</td>
						<td class="text-center">
							<a href="/admin/product_stock_units/update/{{ stock_unit.id }}/product_id:{{ product.id}}" title="Update"><i class="fa fa-pencil-square fa-2x"></i></a>&nbsp;
							<a href="javascript:void(0)" data-product-id="{{ product.id }}" data-id="{{ stock.id }}" class="delete" title="Hapus"><i class="fa fa-trash-o fa-2x"></i></a>
						</td>
					</tr>
				{% elsefor %}
					<tr>
						<td colspan="3"><i>Belum ada satuan</i></td>
					</tr>
				{% endfor %}
				</tbody>
			</table>
		</div>
	</div>
</div>
<script>
	for (let items = document.querySelectorAll('.delete'), i = items.length; i--; ) {
		items[i].onclick = function() {
			if (!confirm('Anda yakin ingin menghapus product ini ?')) {
				return !1
			}
			var form = document.createElement('form');
			form.method = 'POST',
			form.action = '/admin/product_stock_units/delete/' + this.dataset.id + '/product_id:' + this.dataset.productId,
			document.body.appendChild(form),
			form.submit()
		}
	}
</script>
