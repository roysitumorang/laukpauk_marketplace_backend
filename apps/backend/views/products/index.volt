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
				<a href="/admin/products"><h2>Daftar Produk</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
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
				{{ flashSession.output() }}
				<form action="/admin/products" method="GET" id="search">
					<table class="table table-striped">
						<tr>
							<td>
								Kategori :
								<select name="category_id">
									<option value="">Semua</option>
									{% for category in categories %}
									<option value="{{ category.id}}"{% if category.id == category_id %} selected{% endif %}>{% if category.parent_id %}--{% endif %}{{ category.name }} ({{ category.total_products }})</option>
									{% endfor %}
								</select>
								Status :
								<select name="published">
									<option value="">Semua</option>
									<option value="1"{% if published %} selected{% endif %}>Tampil</option>
									<option value="0"{% if published === 0 %} selected{% endif %}>Tersembunyi</option>
								</select>
								Nama :
								<input type="text" name="keyword" value="{{ keyword }}" placeholder="ID / Nama">&nbsp;
								<input type="submit" value="CARI" class="btn btn-info">
							</td>
						</tr>
					</table>
				</form>
				<i class="fa fa-plus-square"></i>&nbsp;<a href="/admin/products/create" title="Tambah Produk">Tambah Produk</a>
				{% if page.total_items %}
				<span style="float:right"><strong>{{ page.total_items }} Product{% if page.total_items > 1 %}s{% endif %}</strong></span>
				{% endif %}
				<table class="table table-striped">
					<thead>
						<tr>
							<th width="25" class="text-center"><b>No</b></th>
							<th colspan="2" class="text-center"><b>Produk</b></th>
							<th class="text-center"><b>#</b></th>
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
							<td{{ background }} class="text-right">{{ product.rank }}</td>
							<td{{ background }} width="5%">
								<img src="/assets/image/no_picture_120.png" border="0">
							</td>
							<td{{ background }}>
								<font size="4">{{ product.name }}</font><br>
								<strong>ID :</strong>&nbsp;#{{ product.id }}<br>
								<strong>Satuan :</strong>&nbsp;{{ product.stock_unit }}<br>
								{% if product.lifetime %}
								<strong>Masa Pakai :</strong>&nbsp;{{ product.lifetime }} hari<br>
								{% endif %}
								<strong>Kategori :</strong>&nbsp;{{ product.category.name }}
							</td>
							<td{{ background }} class="text-center">
								<a href="/admin/products/{{ product.id}}/update?next={{ next }}" title="Ubah"><i class="fa fa-pencil-square fa-2x"></i></a>
								<a href="javascript:void(0)" class="published" data-id="{{ product.id }}" data-published="{{ product.published }}">
									{% if product.published %}
									<i class="fa fa-eye fa-2x"></i>
									{% else %}
									<font color="#FF0000"><i class="fa fa-eye-slash fa-2x"></i></font>
									{% endif %}
								</a>
								<a href="javascript:void(0)" data-id="{{ product.id }}" class="delete" title="Hapus"><i class="fa fa-trash-o fa-2x"></i></a>
							</td>
						</tr>
					{% elsefor %}
						<tr>
							<td colspan="4"><i>Belum ada produk</i></td>
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
							<a href="/admin/products/index{% if category_id %}/category_id:{{ category_id }}{% endif %}{% if is_int(published) %}/published:{{ published }}{% endif %}{% if keyword %}/keyword:{{ keyword }}{% endif %}{% if i > 1 %}/page:{{ i }}{% endif %}">{{ i }}</a>
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
	let items = document.querySelectorAll('.published,.delete'), i = items.length, search = document.getElementById('search'), url = '/admin/products/index', replacement = {' ': '+', ':': '', '\/': ''};
	search.addEventListener('submit', event => {
		event.preventDefault();
		if (search.category_id.value) {
			url += '/category_id:' + search.category_id.value;
		}
		if (search.published.value) {
			url += '/published:' + search.published.value;
		}
		if (search.keyword.value) {
			url += '/keyword:' + search.keyword.value.trim().replace(/ |:|\//g, match => {
				return replacement[match];
			});
		}
		location.href = url;
	}, false);
	for ( ; i--; ) {
		let item = items[i];
		item.onclick = () => {
			if ('delete' === item.className && !confirm('Anda yakin ingin menghapus product ini ?')) {
				return !1
			}
			let form = document.createElement('form');
			form.method = 'POST',
			form.action = 'delete' === item.className
			? '/admin/products/' + item.dataset.id + '/delete'
			: '/admin/products/' + item.dataset.id + '/' + (item.dataset.published == 1 ? 'unpublish' : 'publish') + '?next=' + window.location.href.split('#')[0] + '#' + item.dataset.id,
			document.body.appendChild(form),
			form.submit()
		}
	}
</script>
