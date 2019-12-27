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
				<a href="/admin/users/{{ user.id }}/products/update{% if page.current > 1%}/page:{{ page.current }}{% endif %}"><h2>Update Produk</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span><a href="/admin/users">Daftar Member</a></span></li>
						<li><span><a href="/admin/users/{{ user.id }}">{{ user.name }}</a></span></li>
						<li><span>Update Produk</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Update Produk {{ user.name }}</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				<div class="tabs">
					{{ partial('partials/tabs_user', ['user': user, 'expand': 'products']) }}
					<div class="tab-content">
						<div id="user_products" class="tab-pane active">
							{{ flashSession.output() }}
							<form method="GET" action="/admin/users/{{ user.id }}/products/update" id="search">
								<table class="table table-striped">
									<tr>
										<td>
											<input type="text" name="keyword" value="{{ keyword }}" placeholder="Cari">
											<button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Cari</button>
										</td>
									</tr>
								</table>
							</form>
							{% if user_products %}
							<form method="POST" action="/admin/users/{{ user.id }}/products/update{% if page.current > 1 %}/page:{{ page.current }}{% endif %}">
							{% endif %}
								<table class="table table-striped">
									<thead>
										<tr>
											<th class="text-center" width="5%">No</th>
											<th class="text-center">Kategori</th>
											<th class="text-center">Produk</th>
											<th class="text-center">Harga</th>
											<th class="text-center">Stok</th>
										</tr>
									</thead>
									<tbody>
									{% for user_product in user_products %}
										<tr>
											<td class="text-right">
												{{ user_product.rank }}
												<input type="hidden" name="id[]" value="{{ user_product.id }}">
											</td>
											<td>{{ user_product.category }}</td>
											<td>{{ user_product.name }} ({{ user_product.stock_unit }})</td>
											<td class="text-center"><input type="text" name="price[]" value="{{ user_product.price }}"></td>
											<td class="text-center"><input type="text" name="stock[]" value="{{ user_product.stock }}"></td>
										</tr>
									{% elsefor %}
										<tr>
											<td colspan="5"><i>Belum ada produk</i></td>
										</tr>
									{% endfor %}
									{% if user_products %}
										<tr>
											<td colspan="5" class="text-right">
												<button type="submit" class="btn btn-info">SIMPAN</button>
											</td>
										</tr>
									{% endif %}
									</tbody>
								</table>
							{% if user_products %}
							</form>
							{% endif %}
							{% if page.last > 1 %}
							<div class="weepaging">
								<p>
									<b>Halaman:</b>&nbsp;&nbsp;
									{% for i in pages %}
										{% if i == page.current %}
										<b>{{ i }}</b>
										{% else %}
										<a href="/admin/users/{{ user.id }}/products/update{% if keyword %}/keyword:{{ keyword }}{% endif %}{% if i > 1 %}/page:{{ i }}{% endif %}">{{ i }}</a>
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
		</section>
	{{ partial('partials/right_side') }}
</section>
<script>
	let search = document.getElementById('search'), url = search.action, replacement = {' ': '+', ':': '', '\/': ''};
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