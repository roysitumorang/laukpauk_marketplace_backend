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
				<a href="/admin/orders/create"><h2>Tambah Order</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span><a href="/admin/orders">Order List</a></span></li>
						<li><span><a href="/admin/orders/create/buyer_id:{{ buyer.id }}">Tambah</a></span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Tambah Order</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				{{ flashSession.output() }}
				{{ form('/admin/orders/create/buyer_id:' ~ buyer.id ~ (product_category_id ? '/product_category_id:' ~ product_category_id : '') ~ (keyword ? '/keyword:' ~ keyword : '') ~ (pagination.current > 1 ? '/page:' ~ pagination.current : '')) }}
					<table class="table table-striped">
						<tr>
							<td>
								<b><font color="#000099"><i class="fa fa-user"></i> Pembeli :</font></b>
							</td>
							<td>
								{{ text_field('name', 'value': buyer.name, 'placeholder': 'Nama', 'class': 'form-control') }}
							</td>
							<td>
								<b><font color="#000099"><i class="fa fa-mobile"></i> Nomor HP :</font></b>
							</td>
							<td>
								{{ buyer.mobile_phone }}
							</td>
						</tr>
						<tr>
							<td>
								<b><font color="#000099"><i class="fa fa-map-marker"></i> Alamat :</font></b>
							</td>
							<td colspan="3">
								{{ text_field('address', 'value': buyer.address, 'placeholder': 'Alamat', 'class': 'form-control') }}
							</td>
						</tr>
						<tr>
							<td>
								<b><font color="#000099"><i class="fa fa-home"></i> Kelurahan :</font></b>
							</td>
							<td>
								{{ buyer.village.name }}
							</td>
							<td>
								<b><font color="#000099"><i class="fa fa-map"></i> Kecamatan :</font></b>
							</td>
							<td>
								{{ buyer.village.subdistrict.name }}
							</td>
						</tr>
						{% if order_products %}
						<tr>
							<td class="text-nowrap">
								<b><font color="#000099"><i class="fa fa-calendar"></i> Pengantaran :</font></b>
							</td>
							<td>
								{{ select_static('scheduled_delivery', delivery_datetimes, 'value': order.scheduled_delivery) }}
							</td>
							<td>
								{% if coupons %}
									<b><font color="#000099"><i class="fa fa-tag"></i> Voucher :</font></b>
								{% endif %}
							</td>
							<td>
								{% if coupons %}
									{{ select_static('coupon_id', coupons, 'value': order.coupon_id, 'useEmpty': true, 'emptyText': '-', 'emptyValue': '') }}
								{% endif %}
							</td>
						</tr>
						<tr>
							<td>
								<b><font color="#000099"><i class="fa fa-shopping-cart"></i> Produk :</font></b>
							</td>
							<td></td>
							<td></td>
							<td></td>
						</tr>
						{% for item in order_products %}
						<tr>
							<td class="text-nowrap">
								<b>{{ item.name }}</b>
								{% if !buyer.merchant_id %}
									<br>
									<i class="fa fa-user"></i> {{ item.company }}
									<br>
									<i class="fa fa-mobile"></i> {{ item.mobile_phone }}
									<br>
									<i class="fa fa-map-marker"></i> {{ item.address }}
								{% endif %}
							</td>
							<td class="text-nowrap">
								{{ select_static('quantity[' ~ item.id ~ ']', item.quantities, 'value': item.quantity, 'id': 'quantity_' ~ item.id, 'class': 'quantity', 'data-id': item.id) }}
								x Rp. {{ item.price | number_format }} @ {{ item.stock_unit }}
							</td>
							<td><b>Rp. {{ (item.quantity * item.price) | number_format }}</b></td>
							<td>
								<a type="button" class="remove btn btn-danger btn-sm" data-id="{{ item.id }}"><i class="fa fa-cart-arrow-down"></i></a>
							</td>
						</tr>
						{% endfor %}
						<tr>
							<td>
								<b><font color="#000099"><i class="fa fa-shopping-basket"></i> Subtotal :</font></b>
								<br>
								<b>Rp. {{ order.original_bill | number_format }}</b>
							</td>
							<td>
								<b><font color="#000099"><i class="fa fa-paper-plane"></i> Ongkos Kirim :</font></b>
								<br>
								<b>
								{% if order.shipping_cost %}
									Rp. {{ order.shipping_cost | number_format }}
								{% else %}
									-
								{% endif %}
								</b>
							</td>
							<td>
								<b><font color="#000099"><i class="fa fa-minus-square"></i> Diskon :</font></b>
								<br>
								<b>
								{% if order.discount %}
									Rp. {{ order.discount | number_format }}
								{% else %}
									-
								{% endif %}
								</b>
							</td>
							<td>
								<b><font color="#000099"><i class="fa fa-shopping-bag"></i> Total :</font></b>
								<br>
								<b>Rp. {{ order.final_bill | number_format }}</b>
							</td>
						</tr>
						<tr>
							<td colspan="4"><button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> SIMPAN</button></td>
						</tr>
						{% else %}
						<tr>
							<td colspan="4">Keranjang belanja kosong, silahkan isi dengan produk yang terdaftar di bawah.</td>
						</tr>
						{% endif %}
					</table>
				{{ endForm() }}
				{{ form('/admin/orders/create/buyer_id:' ~ buyer.id, 'method': 'GET', 'id': 'search') }}
					<table class="table table-striped">
						<tr>
							<td bgcolor="#e0ebeb">
								<strong>Kategori Produk</strong>
								{{ select('product_category_id', product_categories, 'using': ['id', 'name'], 'value': product_category_id, 'useEmpty': true, 'emptyText': '- semua kategori -', 'emptyValue': '') }}
								<strong>Nama Produk</strong>
								{{ text_field('keyword', 'value': keyword, 'placeholder': 'Nama produk') }}
								<button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-search"></i> Cari</button>
							</td>
						</tr>
					</table>
				{{ endForm() }}
				{% if products %}
					<div class="row">
					{% for product in products %}
						<div class="col-md-4 panel">
							<div class="panel-body panel-featured text-center">
								{{ form('/admin/orders/add_product/buyer_id:' ~ buyer.id ~ (product_category_id ? '/product_category_id:' ~ product_category_id : '') ~ (keyword ? '/keyword:' ~ keyword : '') ~ (pagination.current > 1 ? '/page:' ~ pagination.current : '')) }}
									<img src="/assets/image/{% if product.picture %}{{ product.thumbnails[0] }}{% else %}no_picture_120.png{% endif %}" border="0" width="150" height="150">
									<br>
									<strong>{{ product.name }}</strong>
									<br>
									Rp. {{ product.price | number_format }} @ {{ product.stock_unit }}
									<br>
									<strong><i class="fa fa-user"></i> {{ product.company }}</strong>
									<br>
									<strong><i class="fa fa-mobile"></i> {{ product.mobile_phone }}</strong>
									<br>
									<strong><i class="fa fa-map-marker"></i> {{ product.address }}</strong>
									<br>
									{{ hidden_field('user_product_id', 'value': product.id) }}
									{{ select_static('quantity', product.quantities) }}
									<button type="submit" class="btn btn-primary"><i class="fa fa-cart-plus"></i></button>
								{{ endForm() }}
							</div>
						</div>
					{% endfor %}
					</div>
				{% else %}
					<div class="panel panel-default">
						<div class="panel-body">Belum ada produk</div>
					</div>
				{% endif %}
				{% if pagination.total_pages > 1 %}
				<div class="weepaging">
					<p>
						<b>Halaman:</b>&nbsp;&nbsp;
						{% for i in pages %}
							{% if i == pagination.current %}
								<b>{{ i }}</b>
							{% else %}
								<a href="/admin/orders/create/buyer_id:{{ buyer.id }}{% if product_category_id %}/product_category_id:{{ product_category_id }}{% endif %}{% if keyword %}/keyword:{{ keyword }}{% endif %}/page:{{ i }}">{{ i }}</a>
							{% endif %}
						{% endfor %}
					</p>
				</div>
				{% endif %}
				<!-- eof Content //-->
			</div>
			<!-- end: page -->
		</section>
	</div>
	{{ partial('partials/right_side') }}
</section>
<script>
	let coupon = document.querySelector('#coupon_id'), scheduled_delivery = document.querySelector('#scheduled_delivery');
	document.querySelector('#search').addEventListener('submit', event => {
		let url = event.target.action, replacement = {' ': '+', ':': '', '\/': ''};
		event.preventDefault(),
		event.target.product_category_id.value && (url += '/product_category_id:' + event.target.product_category_id.value),
		event.target.keyword.value && (url += '/keyword:' + event.target.keyword.value.trim().replace(/ |:|\//g, match => {
			return replacement[match]
		})),
		location.href = url
	}, false),
	coupon && (coupon.addEventListener('change', event => {
		let form = document.createElement('form'), input = document.createElement('input');
		form.method = 'POST',
		form.action = '/admin/orders/apply_coupon/buyer_id:{{ buyer.id }}{% if product_category_id %}/product_category_id:{{ product_category_id }}{% endif %}{% if keyword %}/keyword:{{ keyword }}{% endif %}{% if pagination.current > 1 %}/page:{{ pagination.current }}{% endif %}',
		input.type = 'hidden',
		input.name = event.target.name,
		input.value = event.target.value,
		form.appendChild(input),
		document.body.appendChild(form),
		form.submit()
	}, false)),
	scheduled_delivery && (scheduled_delivery.addEventListener('change', event => {
		let form = document.createElement('form'), input = document.createElement('input');
		form.method = 'POST',
		form.action = '/admin/orders/set_delivery/buyer_id:{{ buyer.id }}{% if product_category_id %}/product_category_id:{{ product_category_id }}{% endif %}{% if keyword %}/keyword:{{ keyword }}{% endif %}{% if pagination.current > 1 %}/page:{{ pagination.current }}{% endif %}',
		input.type = 'hidden',
		input.name = event.target.name,
		input.value = event.target.value,
		form.appendChild(input),
		document.body.appendChild(form),
		form.submit()
	}, false)),
	document.querySelectorAll('.remove').forEach(product => {
		product.addEventListener('click', event => {
			if (!confirm('Anda yakin ingin menghapus produk ini ?')) {
				return !1
			}
			let form = document.createElement('form'), input = document.createElement('input');
			form.method = 'POST',
			form.action = '/admin/orders/remove_product/buyer_id:{{ buyer.id }}{% if product_category_id %}/product_category_id:{{ product_category_id }}{% endif %}{% if keyword %}/keyword:{{ keyword }}{% endif %}{% if pagination.current > 1 %}/page:{{ pagination.current }}{% endif %}',
			input.type = 'hidden',
			input.name = 'user_product_id',
			input.value = event.target.parentNode.dataset.id,
			form.appendChild(input),
			document.body.appendChild(form),
			form.submit()
		}, false)
	}),
	document.querySelectorAll('.quantity').forEach(product => {
		product.addEventListener('click', event => {
			let form = document.createElement('form'), input_id = document.createElement('input'), input_quantity = document.createElement('input');
			form.method = 'POST',
			form.action = '/admin/orders/add_product/buyer_id:{{ buyer.id }}{% if product_category_id %}/product_category_id:{{ product_category_id }}{% endif %}{% if keyword %}/keyword:{{ keyword }}{% endif %}{% if pagination.current > 1 %}/page:{{ pagination.current }}{% endif %}',
			input_id.type = 'hidden',
			input_id.name = 'user_product_id',
			input_id.value = event.target.parentNode.dataset.id,
			input_quantity.type = 'hidden',
			input_quantity.name = 'quantity',
			input_quantity.value = event.target.parentNode.value,
			form.appendChild(input_id),
			form.appendChild(input_quantity),
			document.body.appendChild(form),
			form.submit()
		}, false)
	})
</script>