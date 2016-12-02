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
				<a href="/admin/users/update/{{ user.id }}"><h2>Update Member</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span><a href="/admin/users">Member List</a></span></li>
						<li><span><a href="/admin/users/update/{{ user.id }}">Update Member</a></span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Update Member:&nbsp;&nbsp;{{ user.name }}</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				{{ flashSession.output() }}
				<div class="tabs">
					{{ partial('partials/user_tabs', ['user': user, 'expand': 'products']) }}
					<div class="tab-content">
						<div id="areas" class="tab-pane active">
							<form method="POST" action="/admin/product_prices/create/user_id:{{ user.id }}">
								<table class="table table-striped">
									<tr>
										<td class="text-right">
											<b>Kategori :</b>
										</td>
										<td>
											<select id="category_id" class="form form-control">
												{% for category in categories %}
												<option value="{{ category.id }}">{{ category.name }}</option>
												{% endfor %}
											</select>
										</td>
										<td class="text-right">
											<b>Produk :</b>
										</td>
										<td>
											<select name="product_id" id="product_id" class="form form-control">
												{% for product in current_products %}
												<option value="{{ product.id }}">{{ product.name }}</option>
												{% endfor %}
											</select>
										</td>
									</tr>
									<tr>
										<td class="text-right">
											<b>Harga :</b>
										</td>
										<td>
											<input type="text" name="value" value="0" class="form form-control">
										</td>
										<td class="text-right">
											<b>Satuan :</b>
										</td>
										<td>
											<select name="unit_size" class="form form-control form-20">
												{% for size, label in sizes %}
												<option value="{{ size }}">{{ label }}</option>
												{% endfor %}
											</select>&nbsp;
											<span id="stock_unit">{{ current_products[0].stock_unit  }}</span>
										</td>
									</tr>
									<tr>
										<td class="text-right">
											<b>Jam Order Maksimal :</b>
										</td>
										<td>
											<input type="text" name="order_closing_hour" class="form form-control form-30 text-center" size="5">
										</td>
										<td class="text-right"></td>
										<td>
											<button type="submit" class="btn btn-info">TAMBAH</button>
										</td>
									</tr>
								</table>
							</form>
							<form method="POST" action="/admin/service_areas/create/user_id:{{ user.id }}" enctype="multipart/form-data">
								<table class="table table-striped">
									<thead>
										<tr>
											<th width="5%"><b>No</b></th>
											<th><b>Kategori</b></th>
											<th><b>Produk</b></th>
											<th><b>Harga</b></th>
											<th><b>Jam Order Maksimal</b></th>
											<th><b>#</b></th>
										</tr>
									</thead>
									<tbody>
									{% for price in prices %}
										<tr>
											<td>{{ price.rank }}</td>
											<td>{{ price.category }}</td>
											<td>{{ price.product }}</td>
											<td>Rp. {{ number_format(price.value) }} @ {{ price.unit_size }} {{ price.stock_unit }}</td>
											<td class="text-center">{{ price.order_closing_hour|default('-') }}</td>
											<td>
												<a href="javascript:void(0)" data-user-id="{{ user.id }}" data-id="{{ price.id }}" class="publish">{% if !price.published %}<font color="#FF0000">{% endif %}<i class="fa fa-eye fa-2x">{% if !price.published %}</font>{% endif %}</i></a>
												<a href="javascript:void(0)" data-user-id="{{ user.id }}" data-id="{{ price.id }}" class="delete" title="Hapus"><i class="fa fa-trash-o fa-2x"></i></a>
											</td>
										</tr>
									{% elsefor %}
										<tr>
											<td colspan="6"><i>Belum ada produk</i></td>
										</tr>
									{% endfor %}
									</tbody>
								</table>
							</form>
						</div>
					</div>
				</div>
				<!-- eof Content //-->
			</div>
			<!-- end: page -->
		</section>
	</div>
	{{ partial('partials/right_side') }}
</section>
<script>
	var products = {{ products_json }}, category = document.getElementById('category_id'), product = document.getElementById('product_id'), stock_unit = document.getElementById('stock_unit');
	category.onchange = function() {
		var current_products = products[this.value], new_options = '';
		for (var item in current_products) {
			new_options += '<option value="' + current_products[item].id + '">' + current_products[item].name + '</option>';
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
	};
	for (var items = document.querySelectorAll('.delete'), i = items.length; i--; ) {
		items[i].onclick = function() {
			if (!confirm('Anda yakin menghapus data ini ?')) {
				return !1
			}
			var form = document.createElement('form');
			form.method = 'POST',
			form.action = '/admin/product_prices/delete/' + this.dataset.id + '/user_id:' + this.dataset.userId,
			document.body.appendChild(form),
			form.submit()
		}
	}
	for (var items = document.querySelectorAll('.publish'), i = items.length; i--; ) {
		items[i].onclick = function() {
			var form = document.createElement('form');
			form.method = 'POST',
			form.action = '/admin/product_prices/update/' + this.dataset.id + '/user_id:' + this.dataset.userId,
			document.body.appendChild(form),
			form.submit()
		}
	}
</script>