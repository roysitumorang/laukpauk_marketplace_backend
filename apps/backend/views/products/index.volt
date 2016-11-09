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
				<a href="/admin/products"><h2>Produk List</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span>Produk List</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Produk List</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				{{ flashSession.output() }}
				<table class="table table-striped">
					<tr>
						<td>
							<input type="button" name="button" onclick="location.href='/admin/products/create'" value="Tambah Produk Baru" class="btn btn-info">
							<input type="button" name="button" onclick="location.href='products.php?do=updateharga&cat={$Cat}&brand={$Brand}&vCompare={$vCompare}&vTeks={$vTeks}&page={$Page}'" value="Update Harga dan Berat" class="btn btn-warning">
							<input type="button" name="button" onclick="location.href='products.php?do=updatealltype&cat={$Cat}&brand={$Brand}&vCompare={$vCompare}&vTeks={$vTeks}&page={$Page}'" value="Update Type Produk" class="btn btn-primary">
							<input type="button" name="button" onclick="location.href='products.php?do=updatealldimensi&cat={$Cat}&brand={$Brand}&vCompare={$vCompare}&vTeks={$vTeks}&page={$Page}'" value="Update Dimensi Produk" class="btn btn-success">
							<input type="button" name="button" onclick="location.href='products.php?do=updatestock&cat={$Cat}&brand={$Brand}&vCompare={$vCompare}&vTeks={$vTeks}&page={$Page}'" value="Update Stock" class="btn btn-danger">
							<input type="button" name="button" onclick="location.href='products.php?do=updatefoto&cat={$Cat}&brand={$Brand}&vCompare={$vCompare}&vTeks={$vTeks}&page={$Page}'" value="Update Foto" class="btn btn-warning">
						</td>
					</tr>
				</table>
				<form action="/admin/products" method="GET">
					<table class="table table-striped">
						<tr>
							<td>
								<b>Cari berdasarkan:</b>
								<select name="field" class="form form-control form-30">
								{% for key, description in search_fields %}
									<option value="{{ key }}"{% if key == field %} selected{% endif %}>{{ description }}</option>
								{% endfor %}
								</select>&nbsp;&nbsp;
								<input type="text" name="keyword" size="40" value="{{ keyword }}" class="form form-control form-40">&nbsp;
								<input type="submit" value="CARI" class="btn btn-info">
							</td>
						</tr>
					</table>
					<div class="panel panel-default">
						<div class="panel-body">
							<select name="product_category_id" onchange="this.form.submit()" class="form form-control form-30">
							{% for category in categories %}
								<option value="{{ category.id}}"{% if category.id == product_category_id %} selected{% endif %}>{% if category.parent_id %}--{% endif %}{{ category.name }} ({{ category.total_products }})</option>
							{% endfor %}
							</select>
							&nbsp;
							<select name="brand_id" onchange="this.form.submit()" class="form
form-control form-30">
							{% for brand in brands %}
								<option value="{{ brand.id}}"{% if brand.id == brand_id %} selected{% endif %}>{{ brand.name }} ({{ brand.total_products }})</option>
							{% endfor %}
							</select>&nbsp;
							<span style="float:right;padding:3px"><strong>Total Produk:</strong>&nbsp;<font size="3">{{ page.total_items }} produk</font></span>
						</div>
					</div>
				</form>
				<table class="table table-striped">
					<thead>
						<tr>
							<th width="25"><b>No</b></th>
							<th colspan="2"><b>Produk</b></th>
							<th><b>Type Produk</b></th>
							<th><b>##</b></th>
						</tr>
					</thead>
					<tbody>
					{% for product in products %}
						{% if product.published %}
							{% set background = '' %}
						{% else %}
							{% set background = ' style="opacity:0.4;filter:alpha(opacity=40)"' %}
						{% endif %}
						<tr id="{{ product.id }}">
							<td{{ background }}>{{ product.rank }}</td>
							<td{{ background }} width="5%">
								{% if !product.thumbnail %}
								<img src="/assets/images/no_picture_120.png" border="0">
								{% else %}
								<a class="image-popup-no-margins" href="/assets/images/{{ product.pictures[0].name }}"><img src="/assets/images/{{ product.thumbnail }}" width="120" height="100" border="0"></a>
								{% endif %}
							</td>
							<td{{ background }}>
								<a href="/admin/products/show/{{ product.id }}" title="{{ product.name }}">
									<font size="4">#{{ product.code }} - {{ product.name }}</font>
								</a>
								<a href="javascript:void(0)" class="published" data-id="{{ product.id }}">
									{% if product.published %}
									<i class="fa fa-eye fa-2x"></i>
									{% else %}
									<font color="#FF0000"><i class="fa fa-eye-slash fa-2x"></i></font>
									{% endif %}
								</a>
								<br><br>
								<strong><font size="3">Rp. {{ product.price }}</font></strong> /
								{{ product.weight }} gram<br>
								<strong>Category:</strong>&nbsp;{{ product.category.name }}<br>
								<strong>Brand: </strong>{{ product.brand.name }}<br>
								{% if product.status %}
								<i class="fa fa-check-square"></i>&nbsp;Tersedia</a>
								{% else %}
								<a href="/admin/products/update/{{ product.id }}/status:1" title="Set Tersedia" style="color:#FF0000"><i class="fa fa-phone-square"></i>&nbsp;<i>Call Only</i></a>
								{% endif %}
								&nbsp;&nbsp;(Stock:&nbsp;{{ product.stock }})
							</td>
							<td{{ background }}>
								{% for product_variant in product.variants %}
								<a href="/admin/product_variant/update/{{ product.id }}/published:1"{% if product_variant.published %} style="color:#FF0000"{% endif %}>
									<i class="fa fa-angle-right"></i>&nbsp;{{ product_variant.parameter }} {{ product_variant.value }} ({{ product_variant.stock }})
								</a>
								<br>
								{% endfor %}<br>
								<a href="/admin/product_variants/create/product_id:{{ product.id }}"><i class="fa fa-plus-square"></i>&nbsp;Tambah Type</a>
							</td>
							<td{{ background }}>
								<a href="/admin/products/update/{{ product.id}}" title="Ubah"><i class="fa fa-pencil-square fa-2x"></i></a><br>
								<a href="javascript:void(0)" onclick="confirm('Anda yakin ingin menghapus jenis produk ini ?')&&(location.href='/admin/products/delete/{{ product.id }}')" title="Hapus"><i class="fa fa-trash-o fa-2x"></i></a>
							</td>
						</tr>
					{% elsefor %}
						<tr>
							<td colspan="5"><i>Belum ada produk</i></td>
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
							<input type="button" name="button" onclick="location.href='/admin/product_prices/page:{{ i }}'" value="Update Harga dan Berat" class="btn btn-warning">
							<a href="/admin/product_categories/index/page:{{ i }}?{% if product_keyword %}keyword={{ product_keyword }}{% endif %}{% if brand %}&brand={{ brand }}{% endif %}">{{ i }}</a>
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
	for (var items = document.querySelectorAll('.published,.delete'), i = items.length; i--; ) {
		items[i].onclick = function() {
			if ('delete' === this.className && !confirm('Anda yakin ingin menghapus product ini ?')) {
				return !1
			}
			var form = document.createElement('form');
			form.method = 'POST',
			form.action = 'delete' === this.className
			? '/admin/products/delete/' + this.dataset.id
			: '/admin/products/update/' + this.dataset.id + '/published:1?next=' + window.location.href.split('#')[0] + '#' + this.dataset.id,
			document.body.appendChild(form),
			form.submit()
		}
	}
</script>