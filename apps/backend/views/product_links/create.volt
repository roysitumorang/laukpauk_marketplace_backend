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
				<a href="/admin/product_links/index/product_id:{{ product.id }}{% if page.current > 1%}/page:{{ page.current }}{% endif %}"><h2>Tambah Produk Terkait</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span><a href="/admin/products">Daftar Produk</a></span></li>
						<li><span><a href="/admin/products/show/{{ product.id }}">{{ product.name }} ({{ product.stock_unit }})</a></span></li>
						<li><span>Akses Produk</span></li>
						<li><span>Tambah Produk Terkait</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Tambah Produk Terkait {{ product.name }} ({{ product.stock_unit }})</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				<div class="tabs">
					{{ partial('partials/tabs_product', ['active_tab': active_tab, 'product': product]) }}
					<div class="tab-content">
						<div id="accessors" class="tab-pane active">
							{{ flashSession.output() }}
							<p style="margin-left:5px"><i class="fa fa-plus-square"></i>&nbsp;<a href="/admin/product_links/index/product_id:{{ product.id }}{% if page.current > 1 %}/page:{{ page.current }}{% endif %}">Daftar Produk Terkait</a></p>
							<table class="table table-striped">
								<tr>
									<th class="text-center"><b>No</b></th>
									<th class="text-center"><b>Kategori</b></th>
									<th class="text-center"><b>Produk</b></th>
									<th class="text-center"><b>#</b></th>
								</tr>
								{% for nomination in nominations %}
								<tr>
									<td class="text-center">{{ nomination.rank }}</td>
									<td>{{ nomination.category.name }}</td>
									<td>{{ nomination.name }} ({{ nomination.stock_unit }})</td>
									<td class="text-center">
										<form method="POST" action="/admin/product_links/create/product_id:{{ product.id }}{% if page.current > 1 %}/page:{{ page.current }}{% endif %}">
											<input type="hidden" name="linked_product_id" value="{{ nomination.id }}">
											<button type="submit" class="btn btn-info"><i class="fa fa-user-plus"></i> TAMBAH</button>
										</form>
									</td>
								</tr>
								{% endfor %}
							</table>
							{% if page.total_pages > 1 %}
							<div class="weepaging">
								<p>
									<b>Halaman:</b>&nbsp;&nbsp;
									{% for i in pages %}
										{% if i == page.current %}
										<b>{{ i }}</b>
										{% else %}
										<a href="/admin/product_links/create/product_id:{{ product.id }}{% if i > 1 %}/page:{{ i }}{% endif %}">{{ i }}</a>
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
