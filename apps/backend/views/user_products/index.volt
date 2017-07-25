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
				<a href="/admin/users/{{ user.id }}/products{% if page.current > 1%}/index/page:{{ page.current }}{% endif %}"><h2>Produk</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span><a href="/admin/users">Daftar Member</a></span></li>
						<li><span><a href="/admin/users/{{ user.id }}">{{ user.name }}</a></span></li>
						<li><span>Produk</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Produk {{ user.name }}</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				<div class="tabs">
					{{ partial('partials/tabs_user', ['user': user, 'expand': 'products']) }}
					<div class="tab-content">
						<div id="user_products" class="tab-pane active">
							{{ flashSession.output() }}
							<form method="POST" action="/admin/users/{{ user.id }}/products/create">
								<table class="table table-striped">
									<tr>
										<td class="text-right text-nowrap">
											<b>Kategori :</b>
										</td>
										<td>
											{% if user_product.id %}
											{{ user_product.product.category.name }}
											{% else %}
											<select id="category_id">
												{% for category in categories %}
												<option value="{{ category.id }}"{% if category.id == user_product.product.category.id %} selected{% endif %}>{{ category.name }}</option>
												{% endfor %}
											</select>
											{% endif %}
										</td>
										<td class="text-right text-nowrap">
											<b>Produk :</b>
										</td>
										<td colspan="2">
											{% if user_product.id %}
											{{ user_product.product.name }} ({{ user_product.product.stock_unit }})
											{% else %}
											<select name="product_id" id="product_id">
												{% for product in current_products %}
												<option value="{{ product.id }}"{% if product.id == user_product.product.id %} selected{% endif %}>{{ product.name }} ({{ product.stock_unit }})</option>
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
											<input type="text" name="price" value="{{ user_product.price }}" placeholder="Harga">
										</td>
										<td class="text-right">
											<b>Stok :</b>
										</td>
										<td>
											<input type="text" name="stock" value="{{ user_product.stock }}" placeholder="Stok">
										</td>
										<td class="text-right">
											<button type="submit" class="btn btn-primary"><i class="fa fa-plus-square"></i> Tambah</button>
										</td>
									</tr>
								</table>
							</form>
							<form method="GET" action="/admin/users/{{ user.id }}/products/index" id="search">
								<table class="table table-striped">
									<tr>
										<td>
											<input type="text" name="keyword" value="{{ keyword }}" placeholder="Cari">
											<button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Cari</button>
											{% if user_products %}
												<a type="button" href="/admin/users/{{ user.id }}/products/update{% if page.current > 1 %}/page:{{ page.current }}{% endif %}" class="btn btn-primary"><i class="fa fa-pencil"></i> Update Harga &amp; Stok</a>
											{% endif %}
										</td>
									</tr>
								</table>
							</form>
							<table class="table table-striped">
								<thead>
									<tr>
										<th class="text-center" width="5%"><b>No</b></th>
										<th class="text-center"><b>Kategori</b></th>
										<th class="text-center"><b>Produk</b></th>
										<th class="text-center"><b>Harga</b></th>
										<th class="text-center"><b>Stok</b></th>
										<th class="text-center"><b>#</b></th>
									</tr>
								</thead>
								<tbody>
								{% for user_product in user_products %}
									<tr>
										<td class="text-right">{{ user_product.rank }}</td>
										<td>{{ user_product.category }}</td>
										<td>{{ user_product.name }} ({{ user_product.stock_unit }})</td>
										<td>Rp. {{ number_format(user_product.price, 0, ',', '.') }}</td>
										<td class="text-center">{{ user_product.stock }}</td>
										<td class="text-center">
											{% if user_product.price %}
											<a href="javascript:void(0)" data-user-id="{{ user.id }}" data-id="{{ user_product.product_id }}" data-published="{{ user_product.published }}" class="publish">
											{% endif %}
											<i class="fa fa-eye{% if !user_product.published %}-slash{% endif %} fa-2x"></i>
											{% if user_product.price %}
											</a>
											{% endif %}
											<a href="javascript:void(0)" data-user-id="{{ user.id }}" data-id="{{ user_product.product_id }}" class="delete" title="Hapus"><i class="fa fa-trash-o fa-2x"></i></a>
										</td>
									</tr>
								{% elsefor %}
									<tr>
										<td colspan="6"><i>Belum ada produk</i></td>
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
										<a href="/admin/users/{{ user.id }}/products{% if i > 1 %}/index{% if keyword %}/keyword:{{ keyword }}{% endif %}/page:{{ i }}{% endif %}">{{ i }}</a>
										{% endif %}
									{% endfor %}
								</p>
							</div>
							{% endif %}
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
        let products = {{ products | json_encode }}, category = document.getElementById('category_id'), product = document.getElementById('product_id'), stock_unit = document.getElementById('stock_unit'), search = document.getElementById('search'), url = search.action, replacement = {' ': '+', ':': '', '\/': ''};
	for (let items = document.querySelectorAll('.delete'), i = items.length; i--; ) {
		let item = items[i];
		item.onclick = () => {
			if (confirm('Anda yakin menghapus data ini ?')) {
				let form = document.createElement('form');
				form.method = 'POST',
				form.action = '/admin/users/' + item.dataset.userId + '/products/' + item.dataset.id + '/delete?next={{ next }}',
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
			form.action = '/admin/users/' + item.dataset.userId + '/products/' + item.dataset.id + '/' + (item.dataset.published == 1 ? 'unpublish' : 'publish') + '?next={{ next }}',
			document.body.appendChild(form),
			form.submit()
		}
	}
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
	search.addEventListener('submit', event => {
		event.preventDefault();
		if (search.keyword.value) {
			url += '/keyword:' + search.keyword.value.trim().replace(/ |:|\//g, match => {
				return replacement[match];
			});
		}
		location.href = url;
	}, false)
</script>
