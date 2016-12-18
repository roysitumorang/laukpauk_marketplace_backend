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
				<i class="fa fa-plus-square"></i>&nbsp;<a href="/admin/products/create" title="Tambah Produk">Tambah Produk</a><br><br>
				<form action="/admin/products" method="GET">
					<table class="table table-striped">
						<tr>
							<td>
								<br>
								<b>Cari berdasarkan:</b>
							</td>
							<td>
								ID :<br>
								<input type="text" name="id" value="{{ id }}" class="form form-control" placeholder="ID">&nbsp;
							</td>
							<td>
								Nama :<br>
								<input type="text" name="name" value="{{ name }}" class="form form-control" placeholder="Nama">&nbsp;
							</td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td>
								Kategori :<br>
								<select name="product_category_id" class="form form-control">
									<option value="">Semua</option>
									{% for category in categories %}
									<option value="{{ category.id}}"{% if category.id == product_category_id %} selected{% endif %}>{% if category.parent_id %}--{% endif %}{{ category.name }} ({{ category.total_products }})</option>
									{% endfor %}
								</select>
							</td>
							<td>
								Status :<br>
								<select name="published" class="form form-control">
									<option value=""{% if published === null %} selected{% endif %}>Semua</option>
									<option value="1"{% if published %} selected{% endif %}>Tampil</option>
									<option value="0"{% if published === 0 %} selected{% endif %}>Tersembunyi</option>
								</select>
							</td>
							<td colspan="2" class="text-right">
								<br>
								<input type="submit" value="CARI" class="btn btn-info">
							</td>
						</tr>
					</table>
				</form>
				<table class="table table-striped">
					<thead>
						<tr>
							<th width="25" class="text-center"><b>No</b></th>
							<th colspan="2" class="text-center"><b>Produk</b></th>
							<th class="text-center"><b>Satuan</b></th>
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
							<td{{ background }}>{{ product.rank }}</td>
							<td{{ background }} width="5%">
								<img src="/assets/images/no_picture_120.png" border="0">
							</td>
							<td{{ background }}>
								<a href="/admin/products/show/{{ product.id }}" title="{{ product.name }}">
									<font size="4">{{ product.name }}</font>
								</a>
								<a href="javascript:void(0)" class="published" data-id="{{ product.id }}">
									{% if product.published %}
									<i class="fa fa-eye fa-2x"></i>
									{% else %}
									<font color="#FF0000"><i class="fa fa-eye-slash fa-2x"></i></font>
									{% endif %}
								</a>
								<br><br>
								<strong>Kategori:</strong>&nbsp;{{ product.category.name }}<br>
							</td>
							<td{{ background }}>
								<i class="fa fa-plus-square"></i>&nbsp;<a href="/admin/product_stock_units/create/product_id:{{ product.id }}" title="Tambah Satuan">Tambah Satuan</a>
							</td>
							<td{{ background }} class="text-center">
								<a href="/admin/products/update/{{ product.id}}" title="Ubah"><i class="fa fa-pencil-square fa-2x"></i></a><br>
								<a href="javascript:void(0)" data-id="{{ product.id }}" class="delete" title="Hapus"><i class="fa fa-trash-o fa-2x"></i></a>
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
							<a href="/admin/products/index/page:{{ i }}{% if product_keyword %}?keyword={{ product_keyword }}{% endif %}">{{ i }}</a>
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