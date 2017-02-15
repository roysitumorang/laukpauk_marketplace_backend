{{ flashSession.output() }}
<form action="javascript:void(0)" onsubmit="fetch('{{ action }}',{credentials:'include',method:'POST',body:new FormData(this)}).then(response=>{return response.json()}).then(payload=>{payload.status===1?window.location.reload():document.getElementById('form-wrapper').innerHTML=payload.data})">
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