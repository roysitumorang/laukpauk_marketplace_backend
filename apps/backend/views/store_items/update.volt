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
				<a href="/admin/users/{{ user.id }}/store_items/{{ store_item.id }}/update"><h2>Update Produk</h2></a>
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
						<div id="store_items" class="tab-pane active">
							{{ flashSession.output() }}
							<form method="POST" action="/admin/users/{{ user.id }}/store_items/update{% if page.current > 1 %}/page:{{ page.current }}{% endif %}">
								<table class="table table-striped">
									<thead>
										<tr>
											<th class="text-center" width="5%"><b>No</b></th>
											<th class="text-center"><b>Kategori</b></th>
											<th class="text-center"><b>Produk</b></th>
											<th class="text-center"><b>Harga</b></th>
											<th class="text-center"><b>Stok</b></th>
										</tr>
									</thead>
									<tbody>
									{% for store_item in store_items %}
										<tr>
											<td class="text-right">
												{{ store_item.rank }}
												<input type="hidden" name="id[]" value="{{ store_item.id }}">
											</td>
											<td>{{ store_item.category }}</td>
											<td>{{ store_item.name }} ({{ store_item.stock_unit }})</td>
											<td class="text-center"><input type="text" name="price[]" value="{{ store_item.price }}"></td>
											<td class="text-center"><input type="text" name="stock[]" value="{{ store_item.stock }}"></td>
										</tr>
									{% elsefor %}
										<tr>
											<td colspan="5"><i>Belum ada produk</i></td>
										</tr>
									{% endfor %}
										<tr>
											<td colspan="5" class="text-right">
												<button type="submit" class="btn btn-info">SIMPAN</button>
											</td>
										</tr>
									</tbody>
								</table>
							</form>
							{% if page.total_pages > 1 %}
							<div class="weepaging">
								<p>
									<b>Halaman:</b>&nbsp;&nbsp;
									{% for i in pages %}
										{% if i == page.current %}
										<b>{{ i }}</b>
										{% else %}
										<a href="/admin/users/{{ user.id }}/store_items{% if i > 1 %}/index/page:{{ i }}{% endif %}">{{ i }}</a>
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