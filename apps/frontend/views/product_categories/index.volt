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
				<a href="/product_categories"><h2>Daftar Kategori Produk</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/"><i class="fa fa-home"></i></a></li>
						<li><span>Daftar Kategori Produk</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Daftar Kategori Produk</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				{{ flashSession.output() }}
				<table class="table table-striped">
					<tr>
						<td>
							<form method="GET" action="/product_categories" id="search">
								<input type="text" name="keyword" value="{{ keyword }}" size="40" placeholder="Nama">&nbsp;
								<button type="submit" class="btn btn-info">CARI</button>
							</form>
						</td>
					</tr>
				</table>
				<p style="margin-left:5px"><i class="fa fa-plus-square"></i>&nbsp;<a href="/product_categories/create">Tambah Kategori</a></p>
				<table class="table table-striped">
					<thead>
						<tr>
							<th width="1%"><b>No</b></th>
							<th><b>Nama</b></th>
							<th><b>Permalink</b></th>
							<th><b>#</b></th>
						</tr>
					</thead>
					<tbody>
					{% for category in categories %}
						<tr id="{{ category.id }}">
							<td>{{ category.rank }}</td>
							<td>
								{% if category.picture %}
									<a class="image-popup-no-margins" href="/assets/image/{{ category.picture }}">
										<img src="{{ category.thumbnail }}" border="0">
									</a>
									<br>
								{% endif %}
								<a href="/products/index/category_id:{{ category.id }}" title="{{ category.name }}" target="_blank">{{ category.name }} ({{ category.total_products }})</a>
							</td>
							<td>
								<i>{{ category.permalink }}</i>
							</td>
							<td>
								<a href="javascript:void(0)" class="published" data-id="{{ category.id }}" data-published="{{ category.published }}">
									<i class="fa fa-eye{% if !category.published %}-slash{% endif %} fa-2x"></i>
								</a>
								<a href="/product_categories/{{ category.id }}/update" title="Ubah"><i class="fa fa-pencil-square fa-2x"></i></a>
								{% if !category.total_products %}
								<a href="javascript:void(0)" class="delete" data-id="{{ category.id }}" title="Hapus"><i class="fa fa-trash-o fa-2x"></i></a>
								{% endif %}
							</td>
						</tr>
					{% elsefor %}
						<tr>
							<td colspan="3"><i>Belum ada kategori</i></td>
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
							<a href="/product_categories/index{% if keyword %}/keyword:{{ keyword }}{% endif %}{% if i > 1 %}/page:{{ i }}{% endif %}">{{ i }}</a>
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
	let items = document.querySelectorAll('.published,.delete'), i = items.length, search = document.getElementById('search'), url = '/product_categories/', replacement = {' ': '+', ':': '', '\/': ''};
	for ( ; i--; ) {
		let item = items[i];
		items[i].onclick = () => {
			if ('delete' === item.className && !confirm('Anda yakin ingin menghapus kategori ini ?')) {
				return !1
			}
			let form = document.createElement('form');
			form.method = 'POST',
			form.action = 'delete' === item.className
			? url + item.dataset.id + '/delete'
			: url + item.dataset.id + '/' + (item.dataset.published == 1 ? 'unpublish' : 'publish') + '?next=' + window.location.href.split('#')[0] + '#' + item.dataset.id,
			document.body.appendChild(form),
			form.submit()
		}
	}
	search.addEventListener('submit', event => {
		event.preventDefault();
		url += 'index';
		if (search.keyword.value) {
			url += '/keyword:' + search.keyword.value.trim().replace(/ |:|\//g, match => {
				return replacement[match];
			});
		}
		location.href = url;
	}, false)
</script>