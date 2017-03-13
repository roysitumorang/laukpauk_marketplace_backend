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
				<a href="/admin/users/{{ user.id }}/store_items"><h2>Produk</h2></a>
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
						<div id="store_items" class="tab-pane active">
							{{ flashSession.output() }}
							<p style="margin-left:5px">
								<a type="button" href="/admin/users/{{ user.id }}/store_items/create{% if page.current > 1 %}?page={{ page.current }}{% endif %}" class="btn btn-info"><i class="fa fa-plus-square"></i> Tambah Produk</a>
								{% if store_items %}
								<a type="button" href="/admin/users/{{ user.id }}/store_items/update{% if page.current > 1 %}/page:{{ page.current }}{% endif %}" class="btn btn-info"><i class="fa fa-pencil"></i> UPDATE HARGA &amp; STOK</a>
								{% endif %}
							</p>
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
								{% for store_item in store_items %}
									<tr>
										<td class="text-right">{{ store_item.rank }}</td>
										<td>{{ store_item.category }}</td>
										<td>{{ store_item.name }} ({{ store_item.stock_unit }})</td>
										<td>Rp. {{ number_format(store_item.price, 0, ',', '.') }}</td>
										<td class="text-center">{{ store_item.stock }}</td>
										<td class="text-center">
											{% if store_item.price %}
											<a href="javascript:void(0)" data-user-id="{{ user.id }}" data-id="{{ store_item.product_id }}" data-published="{{ store_item.published }}" class="publish">
											{% endif %}
											<i class="fa fa-eye{% if !store_item.published %}-slash{% endif %} fa-2x"></i>
											{% if store_item.price %}
											</a>
											{% endif %}
											<a href="javascript:void(0)" data-user-id="{{ user.id }}" data-id="{{ store_item.product_id }}" class="delete" title="Hapus"><i class="fa fa-trash-o fa-2x"></i></a>
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
			<!-- end: page -->
		</section>
	</div>
	{{ partial('partials/right_side') }}
</section>
<script>
	for (let items = document.querySelectorAll('.delete'), i = items.length; i--; ) {
		let item = items[i];
		item.onclick = () => {
			if (confirm('Anda yakin menghapus data ini ?')) {
				let form = document.createElement('form');
				form.method = 'POST',
				form.action = '/admin/users/' + item.dataset.userId + '/store_items/' + item.dataset.id + '/delete{% if page.current > 1%}?page={{ page.current }}{% endif %}',
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
			form.action = '/admin/users/' + item.dataset.userId + '/store_items/' + item.dataset.id + '/' + (item.dataset.published == 1 ? 'unpublish' : 'publish') + '{% if page.current > 1%}?page={{ page.current }}{% endif %}',
			document.body.appendChild(form),
			form.submit()
		}
	}
</script>
