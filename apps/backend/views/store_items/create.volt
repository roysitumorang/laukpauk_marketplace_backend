<section class="body">
	<!-- start: header -->
	{{ partial('partials/top_menu') }}
	<!-- end: header -->
	<div class="inner-wrapper">
		<!-- start: sidebar -->
		{{ partial('partials/left_side') }}
		<!-- end: sidebar -->
		<section role="main" class="content-body">
			<header class="page-header">
				<a href="/admin/users/{{ user.id }}/store_items/create"><h2>Tambah Produk</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span><a href="/admin/users">Daftar Member</a></span></li>
						<li><span><a href="/admin/users/{{ user.id }}">{{ user.name }}</a></span></li>
						<li><span>Tambah Produk</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Tambah Produk {{ user.name }}</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				<div class="tabs">
					{{ partial('partials/tabs_user', ['user': user, 'expand': 'products']) }}
					<div class="tab-content">
						<div id="store_items" class="tab-pane active">
							{{ flashSession.output() }}
							<form method="POST" action="/admin/{{ user.id }}/store_items/create">
								<table class="table table-striped">
									<tr>
										<td class="text-right">
											<b>Kategori :</b>
										</td>
										<td>
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
									</tr>
									<tr>
										<td class="text-right">
											<b>Produk :</b>
										</td>
										<td>
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
									</tr>
									<tr>
										<td class="text-right">
											<b>Harga :</b>
										</td>
										<td>
											<input type="text" name="price" value="{{ store_item.price }}" placeholder="Harga">
										</td>
									</tr>
									<tr>
										<td class="text-right">
											<b>Stok :</b>
										</td>
										<td>
											<input type="text" name="stock" value="{{ store_item.stock }}" placeholder="Stok">
										</td>
									</tr>
									<tr>
										<td></td>
										<td class="text-right">
											<button type="submit" class="btn btn-info">TAMBAH</button>
										</td>
									</tr>
								</table>
							</form>
						</div>
					</div>
				</div>
				<!-- eof Content //-->
			</div>
		</section>
	{{ partial('partials/right_side') }}
</section>
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