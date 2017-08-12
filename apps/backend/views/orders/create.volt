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
				<form method="POST" action="/admin/orders/create/buyer_id:{{ buyer.id }}{% if product_category_id %}/product_category_id:{{ product_category_id }}{% endif %}{% if keyword %}/keyword:{{ keyword }}{% endif %}{% if page.current > 1 %}/page:{{ page.current }}{% endif %}">
					<table class="table table-striped">
						<tr>
							<td>
								<b><font color="#000099"><i class="fa fa-user"></i> Pembeli :</font></b>
							</td>
							<td>
								<input type="text" name="name" value="{{ buyer.name }}" placeholder="Nama" class="form-control">
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
								<input type="text" name="address" value="{{ buyer.address }}" placeholder="Alamat" class="form-control">
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
								<select name="scheduled_delivery" id="scheduled_delivery">
									{% for delivery_datetime in delivery_datetimes %}
										<option value="{{ delivery_datetime }}"{% if delivery_datetime == order.scheduled_delivery %} selected{% endif %}>{{ delivery_datetime }}</option>
									{% endfor %}
								</select>
							</td>
							<td>
								{% if coupons %}
									<b><font color="#000099"><i class="fa fa-tag"></i> Voucher :</font></b>
								{% endif %}
							</td>
							<td>
								{% if coupons %}
									<select name="coupon_id" id="coupon_id">
										<option value=""></option>
										{% for coupon in coupons %}
											<option value="{{ coupon.id }}"{% if coupon.id == order.coupon_id %} selected{% endif %}>{{ coupon.code }} / diskon {% if coupon.discount_type == 1 %}{{ number_format(coupon.price_discount) }}{% else %}{{ coupon.price_discount }} %{% endif %}{% if coupon.minimum_purchase %} / min. order {{ number_format(coupon.minimum_purchase) }}{% endif %})</option>
										{% endfor %}
									</select>
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
								<select name="quantity[{{ item.id }}]" class="quantity" data-id="{{ item.id }}">
								{% for i in 1..10 %}
									{% if i > item.stock %}
										{% break %}
									{% endif %}
									<option value="{{ i }}"{% if i == item.quantity%} selected{% endif %}>{{ i }}</option>
								{% endfor %}
								</select>
								x Rp. {{ number_format(item.price) }} @ {{ item.stock_unit }}
							</td>
							<td><b>Rp. {{ number_format(item.quantity * item.price) }}</b></td>
							<td>
								<a type="button" class="remove btn btn-danger btn-sm" data-id="{{ item.id }}"><i class="fa fa-cart-arrow-down"></i></a>
							</td>
						</tr>
						{% endfor %}
						<tr>
							<td>
								<b><font color="#000099"><i class="fa fa-shopping-basket"></i> Subtotal :</font></b>
								<br>
								<b>Rp. {{ number_format(order.original_bill) }}</b>
							</td>
							<td>
								<b><font color="#000099"><i class="fa fa-paper-plane"></i> Ongkos Kirim :</font></b>
								<br>
								<b>
								{% if order.shipping_cost %}
									Rp. {{ number_format(order.shipping_cost) }}
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
									Rp. {{ number_format(order.discount) }}
								{% else %}
									-
								{% endif %}
								</b>
							</td>
							<td>
								<b><font color="#000099"><i class="fa fa-shopping-bag"></i> Total :</font></b>
								<br>
								<b>Rp. {{ number_format(order.final_bill) }}</b>
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
				</form>
				<form method="GET" action="/admin/orders/create/buyer_id:{{ buyer.id }}" id="search">
					<table class="table table-striped">
						<tr>
							<td bgcolor="#e0ebeb">
								<strong>Kategori Produk</strong>
								<select name="product_category_id">
									<option value="">Semua Kategori</option>
									{% for item in product_categories %}
										<option value="{{ item.id }}"{% if item.id == product_category_id %} selected{% endif %}>{{ item.name }} ({{ item.total_products }})</option>
									{% endfor %}
								</select>
								<strong>Nama Produk</strong>
								<input type="text" name="keyword" value="{{ keyword }}" placeholder="Nama produk">
								<button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-search"></i> Cari</button>
							</td>
						</tr>
					</table>
				</form>
				{% if products %}
					<div class="row">
					{% for product in products %}
						<div class="col-md-4 panel">
							<div class="panel-body panel-featured text-center">
								<form method="POST" action="/admin/orders/add_product/buyer_id:{{ buyer.id }}{% if product_category_id %}/product_category_id:{{ product_category_id }}{% endif %}{% if keyword %}/keyword:{{ keyword }}{% endif %}{% if page.current > 1 %}/page:{{ page.current }}{% endif %}">
									<img src="/assets/image/{% if product.picture %}{{ product.thumbnails[0] }}{% else %}no_picture_120.png{% endif %}" border="0" width="150" height="150">
									<br>
									<strong>{{ product.name }}</strong>
									<br>
									Rp. {{ number_format(product.price) }} @ {{ product.stock_unit }}
									<br>
									<strong><i class="fa fa-user"></i> {{ product.company }}</strong>
									<br>
									<strong><i class="fa fa-mobile"></i> {{ product.mobile_phone }}</strong>
									<br>
									<strong><i class="fa fa-map-marker"></i> {{ product.address }}</strong>
									<br>
									<input type="hidden" name="user_product_id" value="{{ product.id }}">
									<select name="quantity">
									{% for i in 1..10 %}
										{% if i > product.stock %}
											{% break %}
										{% endif %}
										<option value="{{ i }}">{{ i }}</option>
									{% endfor %}
									</select>
									<button type="submit" class="btn btn-primary"><i class="fa fa-cart-plus"></i></button>
								</form>
							</div>
						</div>
					{% endfor %}
					</div>
				{% else %}
					<div class="panel panel-default">
						<div class="panel-body">Belum ada produk</div>
					</div>
				{% endif %}
				{% if page.total_pages > 1 %}
				<div class="weepaging">
					<p>
						<b>Halaman:</b>&nbsp;&nbsp;
						{% for i in pages %}
							{% if i == page.current %}
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
	let search = document.getElementById('search'), coupon_id = document.getElementById('coupon_id'), replacement = {' ': '+', ':': '', '\/': ''};
	search.addEventListener('submit', event => {
		let url = search.action;
		if (search.product_category_id.value) {
			url += '/product_category_id:' + search.product_category_id.value;
		}
		if (search.keyword.value) {
			url += '/keyword:' + search.keyword.value.trim().replace(/ |:|\//g, match => {
				return replacement[match];
			});
		}
		event.preventDefault(),
		location.href = url
	}, false),
	coupon_id.onchange = () => {
		let form = document.createElement('form'), input = document.createElement('input');
		form.method = 'POST',
		form.action = '/admin/orders/apply_coupon/buyer_id:{{ buyer.id }}{% if product_category_id %}/product_category_id:{{ product_category_id }}{% endif %}{% if keyword %}/keyword:{{ keyword }}{% endif %}{% if page.current > 1 %}/page:{{ page.current }}{% endif %}',
		input.type = 'hidden',
		input.name = 'coupon_id',
		input.value = coupon_id.value,
		form.appendChild(input),
		document.body.appendChild(form),
		form.submit()
	},
	scheduled_delivery.onchange = () => {
		let form = document.createElement('form'), input = document.createElement('input');
		form.method = 'POST',
		form.action = '/admin/orders/set_delivery/buyer_id:{{ buyer.id }}{% if product_category_id %}/product_category_id:{{ product_category_id }}{% endif %}{% if keyword %}/keyword:{{ keyword }}{% endif %}{% if page.current > 1 %}/page:{{ page.current }}{% endif %}',
		input.type = 'hidden',
		input.name = 'scheduled_delivery',
		input.value = scheduled_delivery.value,
		form.appendChild(input),
		document.body.appendChild(form),
		form.submit()
	},
	document.querySelectorAll('.remove').forEach(product => {
		product.onclick = () => {
			if (!confirm('Anda yakin ingin menghapus produk ini ?')) {
				return !1
			}
			let form = document.createElement('form'), input = document.createElement('input');
			form.method = 'POST',
			form.action = '/admin/orders/remove_product/buyer_id:{{ buyer.id }}{% if product_category_id %}/product_category_id:{{ product_category_id }}{% endif %}{% if keyword %}/keyword:{{ keyword }}{% endif %}{% if page.current > 1 %}/page:{{ page.current }}{% endif %}',
			input.type = 'hidden',
			input.name = 'user_product_id',
			input.value = product.dataset.id,
			form.appendChild(input),
			document.body.appendChild(form),
			form.submit()
		}
	}),
	document.querySelectorAll('.quantity').forEach(product => {
		product.onchange = () => {
			let form = document.createElement('form'), input_id = document.createElement('input'), input_quantity = document.createElement('input');
			form.method = 'POST',
			form.action = '/admin/orders/add_product/buyer_id:{{ buyer.id }}{% if product_category_id %}/product_category_id:{{ product_category_id }}{% endif %}{% if keyword %}/keyword:{{ keyword }}{% endif %}{% if page.current > 1 %}/page:{{ page.current }}{% endif %}',
			input_id.type = 'hidden',
			input_id.name = 'user_product_id',
			input_id.value = product.dataset.id,
			input_quantity.type = 'hidden',
			input_quantity.name = 'quantity',
			input_quantity.value = product.value,
			form.appendChild(input_id),
			form.appendChild(input_quantity),
			document.body.appendChild(form),
			form.submit()
		}
	})
</script>