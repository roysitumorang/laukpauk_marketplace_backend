<!-- Content //-->
<div class="tabs">
	{{ partial('partials/tabs_user', ['user': user, 'expand': 'products']) }}
	<div class="tab-content">
		<div id="store_items" class="tab-pane active">
			{{ flashSession.output() }}
			<form method="POST" action="{{ action }}{% if page.current > 1 %}/page:{{ page.current }}{% endif %}">
				<table class="table table-striped">
					<tr>
						<td class="text-right">
							<b>Kategori :</b>
							{% if store_item.id %}
							{{ store_item.product.category.name }}
							{% else %}
							<select id="category_id">
								{% for category in categories %}
								<option value="{{ category.id }}"{% if category.id == store_item.product.category.id %} selected{% endif %}>{{ category.name }}</option>
								{% endfor %}
							</select>
							{% endif %}
						</td>
						<td class="text-right">
							<b>Harga :</b>
							<input type="text" name="price" value="{{ store_item.price }}" placeholder="Harga">
						</td>
						<td class="text-right">
							<b>Stok :</b>
							<input type="text" name="stock" value="{{ store_item.stock }}" placeholder="Stok">
						</td>
					</tr>
					<tr>
						<td class="text-right">
							<b>Produk :</b>
							{% if store_item.id %}
							{{ store_item.product.name }} ({{ store_item.product.stock_unit }})
							{% else %}
							<select name="product_id" id="product_id">
								{% for product in current_products %}
								<option value="{{ product.id }}"{% if product.id == store_item.product.id %} selected{% endif %}>{{ product.name }} ({{ product.stock_unit }})</option>
								{% endfor %}
							</select>
							{% endif %}
						</td>
						<td class="text-right">
							<b>Jam Order Maksimal :</b>
							<select name="order_closing_hour">
								<option value="">-</option>
								{% for hour, label in order_closing_hours %}
								<option value="{{ hour }}"{% if hour == store_item.order_closing_hour %} selected{% endif %}>{{ label }}</option>
								{% endfor %}
							</select>
						</td>
						<td class="text-right">
							<button type="submit" class="btn btn-info">SIMPAN</button>
						</td>
					</tr>
				</table>
			</form>
		</div>
	</div>
</div>
{{ partial('partials/list_store_items', ['page': page, 'pages': pages, 'user': user, 'store_items': store_items]) }}
<script>
	let products = {{ products | json_encode }}, category = document.getElementById('category_id'), product = document.getElementById('product_id'), stock_unit = document.getElementById('stock_unit');
	category.onchange = () => {
		let current_products = products[category.value], new_options = '';
		for (let i in current_products) {
			new_options += '<option value="' + current_products[i].id + '">' + current_products[i].name + ' (' + current_products[i].stock_unit + ')</option>'
		}
		product.innerHTML = new_options,
		stock_unit.innerText = current_products[0].stock_unit,
		product.onchange = () => {
			for (let i in current_products) {
				if (current_products[i].id == product.value) {
					stock_unit.innerText = current_products[i].stock_unit;
					break;
				}
			}
		}
	}
</script>
<!-- eof Content //-->