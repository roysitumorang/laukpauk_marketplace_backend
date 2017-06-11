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
				<a href="/admin/product_groups/create"><h2>Tambah Produk Ke Group</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span><a href="/admin/product_groups">Group Produk</a></span></li>
						<li><span>Tambah Produk Ke Group</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Tambah Produk Ke Group</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				<div class="tabs">
					{{ partial('partials/tabs_product_group', ['expand': 'create']) }}
					<div class="tab-content">
						<div id="group" class="tab-pane active">
							{{ flashSession.output() }}
							<form method="GET" action="/admin/product_group_members/create" id="search">
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
										</td>
									</tr>
								</table>
							</form>
							{% if products %}
								<div><i class="fa fa-plus"></i> Checklist produk yang ingin ditambahkan pada slot <strong>{{ group.name }}</strong></div>
								<div><input type="checkbox" onclick="var checked=this.checked;document.querySelectorAll('input[type=checkbox]').forEach(function(item){item.checked=checked})"> <i>Check / Uncheck All</i> | <strong>Total Produk :</strong> {{ count(products) }}</div>
								<form method="POST" action="/admin/product_group_members/create/product_group_id:{{ product_group.id }}{% if product_category_id %}/product_category_id:{{ product_category_id }}{% endif %}{% if keyword %}/keyword:{{ keyword }}{% endif %}{% if page.current > 1 %}/page:{{ page.current }}{% endif %}">
									<div class="row">
									{% for product in products %}
										<div class="col-md-3 panel">
											<div class="panel-body panel-featured text-center">
												<input type="checkbox" name="product_ids[]" value="{{ product.id }}"{% if in_array(product.id, product_ids) %} checked{% endif %}><br>
												<img src="/assets/image/{% if product.picture %}{{ product.thumbnails[0] }}{% else %}no_picture_120.png{% endif %}" border="0" width="150" height="150"><br>
												<strong>{{ product.name }}<br>({{ product.stock_unit }})</strong><br>
												Rp. {{ number_format(product.price) }}
												{% if !product.published %} <font color="#FF0000"><i class="fa fa-eye-slash"></i></font>{% endif %}
											</div>
										</div>
									{% endfor %}
									</div>
									<a type="button" href="/admin/product_group_members/index/product_group_id:{{ product_group.id }}" class="btn btn-default"><i class="fa fa-chevron-left"></i> Kembali</a>
									<button type="submit" class="btn btn-primary"><i class="fa fa-checked"></i> Tambahkan</button>
								</form>
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
										<a href="/admin/product_group_members/create/product_group_id:{{ product_group.id }}{% if product_category_id %}/product_category_id:{{ product_category_id }}{% endif %}{% if keyword %}/keyword:{{ keyword }}{% endif %}/page:{{ i }}">{{ i }}</a>
										{% endif %}
									{% endfor %}
								</p>
							</div>
							{% endif %}
						</div>
					</div>
				</div>
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