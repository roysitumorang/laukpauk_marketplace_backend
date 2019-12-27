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
				{{ form('/admin/products/index', 'method': 'GET', 'id': 'search') }}
					<table class="table table-striped">
						<tr>
							<td>
								<b>Kategori :</b>
								<br>
								{{ select({'category_id', categories, 'value': category_id, 'useEmpty': true, 'emptyText': '- semua kategori -', 'emptyValue': ''}) }}
							</td>
							<td>
								<b>Status :</b>
								<br>
								{{ select({'published', ['Tersembunyi', 'Tampil'], 'value': published, 'useEmpty': true, 'emptyText': '- semua -', 'emptyValue': ''}) }}
							</td>
							<td>
								<b>ID / Nama :</b>
								<br>
								{{ text_field('keyword', 'value': keyword, 'placeholder': 'ID / Nama') }}
							</td>
							<td>
								<br>
								<button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> CARI</button>
								<a type="button" href="/admin/products/create" class="btn btn-primary"><i class="fa fa-plus-square"></i> Tambah</a>
							</td>
						</tr>
					</table>
				{{ endForm() }}
				{% if pagination.total_items %}
				<span style="float:right"><strong>Total : {{ pagination.total_items }} produk</strong></span>
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
								<img src="{{ product.thumbnail(120) }}" border="0">
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
									<i class="fa fa-eye{% if !product.published %}-slash{% endif %} fa-2x"></i>
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
				{% if pagination.last > 1 %}
				<div class="weepaging">
					<p>
						<b>Halaman:</b>&nbsp;&nbsp;
						{% for i in pages %}
							{% if i == pagination.current %}
								<b>{{ i }}</b>
							{% else %}
								<a href="/admin/products/index{% if category_id %}/category_id:{{ category_id }}{% endif %}{% if ctype_digit(published) %}/published:{{ published }}{% endif %}{% if keyword %}/keyword:{{ keyword }}{% endif %}{% if i > 1 %}/page:{{ i }}{% endif %}">{{ i }}</a>
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
	document.querySelector('#search').addEventListener('submit', event => {
		let url = event.target.action, replacement = {' ': '+', ':': '', '\/': ''};
		event.preventDefault(),
		event.target.category_id.value && (url += '/category_id:' + event.target.category_id.value),
		event.target.published.value && (url += '/published:' + event.target.published.value),
		event.target.keyword.value && (url += '/keyword:' + search.keyword.value.trim().replace(/ |:|\//g, match => {
			return replacement[match]
		})),
		location.href = url
	}, false),
	document.querySelectorAll('.published').forEach(item => {
		item.addEventListener('click', event => {
			let form = document.createElement('form');
			form.method = 'POST',
			form.action = '/admin/products/' + event.target.parentNode.dataset.id + '/toggle_status?next=' + window.location.href.split('#')[0] + '#' + event.target.parentNode.dataset.id,
			document.body.appendChild(form),
			form.submit()
		}, false)
	}),
	document.querySelectorAll('.delete').forEach(item => {
		item.addEventListener('click', event => {
			if (!confirm('Anda yakin ingin menghapus produk ini ?')) {
				return false
			}
			let form = document.createElement('form');
			form.method = 'POST',
			form.action = '/admin/products/' + event.target.parentNode.dataset.id + '/delete',
			document.body.appendChild(form),
			form.submit()
		}, false)
	})
</script>