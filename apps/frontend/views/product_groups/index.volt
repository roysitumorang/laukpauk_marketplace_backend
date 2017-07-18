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
				<a href="/product_groups"><h2>Group Produk</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/"><i class="fa fa-home"></i></a></li>
						<li><span>Group Produk</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Group Produk</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				<div class="tabs">
					{{ partial('partials/tabs_product_group', ['expand': 'group']) }}
					<div class="tab-content">
						<div id="group" class="tab-pane active">
							{{ flashSession.output() }}
							<table class="table table-striped">
								<tr>
									<td>
										<form method="GET" action="/product_groups/index" id="search">
											<input type="text" name="keyword" value="{{ keyword }}" size="40" placeholder="Nama">&nbsp;
											<button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Cari</button>
										</form>
									</td>
								</tr>
							</table>
							<p style="margin-left:5px"><i class="fa fa-plus-square"></i>&nbsp;<a href="/product_groups/create">Tambah Group Produk</a></p>
							<table class="table table-striped">
								<thead>
									<tr>
										<th width="1%">No</th>
										<th>Nama</th>
										<th>Jumlah Produk</th>
										<th>#</th>
									</tr>
								</thead>
								<tbody>
								{% for product_group in product_groups %}
									<tr id="{{ product_group.id }}">
										<td>{{ product_group.rank }}</td>
										<td>{{ product_group.name }}</td>
										<td>{{ product_group.total_products }}</td>
										<td>
											<a href="/product_group_members/index/product_group_id:{{ product_group.id }}"><i class="fa fa-info-circle fa-2x"></i></a>
											<a href="javascript:void(0)" class="published" data-id="{{ product_group.id }}" data-published="{{ product_group.published }}">
												<i class="fa fa-eye{% if !product_group.published %}-slash{% endif %} fa-2x"></i>
											</a>
											<a href="/product_groups/update/{{ product_group.id }}" title="Ubah"><i class="fa fa-pencil-square fa-2x"></i></a>
											{% if !product_group.total_products %}
											<a href="javascript:void(0)" class="delete" data-id="{{ product_group.id }}" title="Hapus"><i class="fa fa-trash-o fa-2x"></i></a>
											{% endif %}
										</td>
									</tr>
								{% elsefor %}
									<tr>
										<td colspan="4"><i>Belum ada group produk</i></td>
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
										<a href="/product_groups/index{% if keyword %}/keyword:{{ keyword }}{% endif %}{% if i > 1 %}/page:{{ i }}{% endif %}">{{ i }}</a>
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
	let items = document.querySelectorAll('.published,.delete'), i = items.length, search = document.getElementById('search'), url = search.action, replacement = {' ': '+', ':': '', '\/': ''};
	for ( ; i--; ) {
		let item = items[i];
		items[i].onclick = () => {
			if ('delete' === item.className && !confirm('Anda yakin ingin menghapus group produk ini ?')) {
				return !1
			}
			let form = document.createElement('form');
			form.method = 'POST',
			form.action = 'delete' === item.className
			? '/product_groups/delete/' + item.dataset.id
			: '/product_groups/' + (item.dataset.published == 1 ? 'un' : '') + 'publish/' + item.dataset.id + '?next=' + window.location.href.split('#')[0] + '#' + item.dataset.id,
			document.body.appendChild(form),
			form.submit()
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
