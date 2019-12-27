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
				<a href="/product_groups"><h2>Daftar Produk</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/"><i class="fa fa-home"></i></a></li>
						<li><a href="/product_groups">Group Produk</a></li>
						<li><span>Daftar Produk</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Daftar Produk</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				<div class="tabs">
					{{ partial('partials/tabs_product_group', ['expand': 'index']) }}
					<div class="tab-content">
						<div id="group" class="tab-pane active">
							{{ flashSession.output() }}
							<form method="GET" action="/product_group_members/index" id="search">
								<table class="table table-striped">
									<tr>
										<td>
											<strong>Group Produk</strong>
											<select name="product_group_id">
												{% for item in product_groups %}
													<option value="{{ item.id }}"{% if item.id == product_group.id %} selected{% endif %}>{{ item.name }} ({{ item.total_products }})</option>
												{% endfor %}
											</select>
											<br>
											<br>
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
											<a type="button" href="/product_group_members/create/product_group_id:{{ product_group.id }}" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Tambah Produk Ke Group</button>
										</td>
									</tr>
								</table>
							</form>
							{% if count(products) %}
								<div class="panel panel-default">
									<div class="panel-body">
										<form method="POST" action="/product_group_members/truncate/product_group_id:{{ group.id }}" onsubmit="return confirm('Hapus semua produk dari group ?')">
											<strong>Total Produk : {{ number_format(page.total_items) }}</strong>
											<button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash-o"></i> Hapus semua produk</button>
										</form>
									</div>
								</div>
								<div class="row">
								{% for product in products %}
									<div class="col-md-3 panel">
										<div class="panel-body panel-featured text-center">
											<img src="/assets/image/{% if product.picture %}{{ product.thumbnails[0] }}{% else %}no_picture_120.png{% endif %}" border="0" width="150" height="150">
											<br>
											<strong>{{ product.name }}<br>({{ product.stock_unit }})</strong><br>
											<form method="POST" action="/product_group_members/delete/product_group_id:{{ product_group.id }}/product_id:{{ product.id }}" onsubmit="return confirm('Hapus produk ini dari group ?')">
												<button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash-o"></i></button>
												{% if !product.published %} <font color="#FF0000"><i class="fa fa-eye-slash"></i></font>{% endif %}
											</form>
										</div>
									</div>
								{% endfor %}
								</div>
							{% else %}
								<div class="panel panel-default">
									<div class="panel-body">Tidak ada produk dalam group</div>
								</div>
							{% endif %}
							{% if page.last > 1 %}
								<div class="weepaging">
									<p>
										<b>Halaman:</b>&nbsp;&nbsp;
										{% for i in pages %}
											{% if i == page.current %}
											<b>{{ i }}</b>
											{% else %}
											<a href="/product_group_members/index/product_group_id:{{ product_group.id }}{% if product_category_id %}/product_category_id:{{ product_category_id }}{% endif %}{% if keyword %}/keyword:{{ keyword }}{% endif %}/page:{{ i }}">{{ i }}</a>
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
	let search = document.getElementById('search'), url = search.action, replacement = {' ': '+', ':': '', '\/': ''};
	search.addEventListener('submit', event => {
		event.preventDefault();
		url += '/product_group_id:' + search.product_group_id.value;
		if (search.product_category_id.value) {
			url += '/product_category_id:' + search.product_category_id.value;
		}
		if (search.keyword.value) {
			url += '/keyword:' + search.keyword.value.trim().replace(/ |:|\//g, match => {
				return replacement[match];
			});
		}
		location.href = url;
	}, false);
</script>