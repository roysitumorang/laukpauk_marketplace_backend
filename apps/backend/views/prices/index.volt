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
				<a href="/admin/prices"><h2>Daftar Harga</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span>Daftar Harga</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Daftar Harga</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				{{ flashSession.output() }}
				<form method="GET" action="/admin/prices/index" id="search">
					<table class="table table-striped">
						<tr>
							<td>
								<input type="text" name="keyword" value="{{ keyword }}" placeholder="Nama Produk">
								<button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> CARI</button>
								<strong>{{ page.total_items }} Produk</strong>
							</td>
						</tr>
					</table>
				</form>
				{% if products %}
				<form method="POST" action="/admin/prices/update{% if keyword %}/keyword:{{ keyword }}{% endif %}{% if page.current > 1 %}/page:{{ page.current }}{% endif %}">
				{% endif %}
					<table class="table table-striped">
						<thead>
							<tr>
								<th width="25" class="text-center">No</th>
								<th class="text-center">Produk</th>
								<th class="text-center">Merchant</th>
								<th class="text-center">Harga</th>
							</tr>
						</thead>
						<tbody>
						{% for product in products %}
							<tr>
								<td class="text-right">{{ product.rank }}</td>
								<td>{{ product.name }} @ {{ product.stock_unit }}</td>
								<td>{{ product.company }}</td>
								<td class="text-center"><input type="text" name="prices[{{ product.id }}]" value="{{ product.price }}"></td>
							</tr>
						{% elsefor %}
							<tr>
								<td colspan="5"><i>Belum ada produk</i></td>
							</tr>
						{% endfor %}
						</tbody>
					</table>
				{% if products %}
					<button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> SIMPAN</button>
				</form>
				{% endif %}
				{% if page.total_pages > 1 %}
				<div class="weepaging">
					<p>
						<b>Halaman:</b>&nbsp;&nbsp;
						{% for i in pages %}
							{% if i == page.current %}
							<b>{{ i }}</b>
							{% else %}
							<a href="/admin/prices/index{% if keyword %}/keyword:{{ keyword }}{% endif %}{% if i > 1 %}/page:{{ i }}{% endif %}">{{ i }}</a>
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
	let search = document.getElementById('search'), url = search.action, replacement = {' ': '+', ':': '', '\/': ''};
	search.addEventListener('submit', event => {
		event.preventDefault();
		if (search.keyword.value) {
			url += '/keyword:' + search.keyword.value.trim().replace(/ |:|\//g, match => {
				return replacement[match];
			});
		}
		location.href = url;
	}, false);
</script>