<form method="POST" action="{{ action }}">
	<table class="table table-striped">
		<tr>
			<td class="text-right">
				<b>Kategori :</b>
			</td>
			<td>
			{% if price.id %}
				{{ price.product.category.name }}
			{% else %}
				<select id="category_id" class="form form-control">
					{% for category in categories %}
					<option value="{{ category.id }}"{% if category.id == price.product.category.id %} selected{% endif %}>{{ category.name }}</option>
					{% endfor %}
				</select>
			{% endif %}
			</td>
			<td class="text-right">
				<b>Produk :</b>
			</td>
			<td>
			{% if price.id %}
				{{ price.product.name }} ({{ price.product.stock_unit }})
			{% else %}
				<select name="product_id" id="product_id" class="form form-control">
					{% for product in current_products %}
					<option value="{{ product.id }}"{% if product.id == price.product.id %} selected{% endif %}>{{ product.name }} ({{ product.stock_unit }})</option>
					{% endfor %}
				</select>
			{% endif %}
			</td>
		</tr>
		<tr>
			<td class="text-right">
				<b>Harga :</b>
			</td>
			<td>
				<input type="text" name="value" value="{{ price.value }}" class="form form-control">
			</td>
			<td class="text-right">
				<b>Jam Order Maksimal :</b>
			</td>
			<td>
				<input type="text" name="order_closing_hour" value="{{ price.order_closing_hour }}" class="form form-control form-30 text-center" size="5">
				<button type="submit" class="btn btn-info">SIMPAN</button>
			</td>
		</tr>
	</table>
</form>
<script>
	var products = {{ products_json }}, category = document.getElementById('category_id'), product = document.getElementById('product_id'), stock_unit = document.getElementById('stock_unit');
	category.onchange = function() {
		var current_products = products[this.value], new_options = '';
		for (var item in current_products) {
			new_options += '<option value="' + current_products[item].id + '">' + current_products[item].name + ' (' + current_products[item].stock_unit + ')</option>';
		}
		product.innerHTML = new_options,
		stock_unit.innerText = current_products[0].stock_unit,
		product.onchange = function() {
			for (var item in current_products) {
				if (current_products[item].id == product.value) {
					stock_unit.innerText = current_products[item].stock_unit;
					break;
				}
			}
		}
	}
</script>