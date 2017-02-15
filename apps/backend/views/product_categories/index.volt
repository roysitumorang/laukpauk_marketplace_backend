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
				<a href="/admin/product_categories"><h2>Category Produk</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li>
							<a href="/admin">
								<i class="fa fa-home"></i>
							</a>
						</li>
						<li><span>Category Produk</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">
					{% if !vTopStatus %}
					Category Produk
					{% else %}
					<a href="/admin/product_categories?keyword={{ product_category_keyword }}&page={{ page }}">Category Produk</a> {{ listLevel }}
					{% endif %}
				</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				{{ flashSession.output() }}
				<p style="margin-left:5px"><i class="fa fa-plus-square"></i>&nbsp;<a href="/admin/product_categories/create">New Category</a></p>
				<table class="table table-striped">
					<tr>
						<td>
							<form method="GET" action="/admin/product_categories">
								<input type="text" name="keyword" value="{{ product_category_keyword }}" size="40" placeholder="Nama">&nbsp;
								<button type="submit" class="btn btn-info">CARI</button>
							</form>
						</td>
					</tr>
				</table>
				<table class="table table-striped">
					<thead>
						<tr>
							<th width="1%"><b>No</b></th>
							<th><b>Category</b></th>
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
										<img src="/assets/image/{{ category.thumbnail }}" border="0">
									</a>
									<br>
								{% endif %}
								<b><font size="4"><a href="/admin/products/index/product_category_id:{{ category.id }}" title="{{ category.name }}" target="_blank">{{ category.name }} ({{ category.total_products }})</a></font></b>
								<a href="javascript:void(0)" class="published" data-id="{{ category.id }}">
									<img src="/backend/images/bullet-{% if category.published %}green{% else %}red{% endif %}.png" border="0">
								</a>
								<br>(<i>{{ category.permalink }}</i>)<br><br>
								<a href="/admin/product_categories/create/parent_id:{{ category.id }}" title="Tambah Sub"><i class="fa fa-plus-square"></i>&nbsp;
								({{ category.total_children }} Sub Category)</a>
							</td>
							<td>
								<a class="popup-with-form" href="#open_{{ category.id }}"><i class="fa fa-info-circle fa-2x"></i></a><br>
								<a href="/admin/product_categories/update/{{ category.id }}" title="Ubah"><i class="fa fa-pencil-square fa-2x"></i></a>
								{% if !category.total_children %}
								<br><a href="javascript:void(0)" class="delete" data-id="{{ category.id }}" title="Hapus"><i class="fa fa-trash-o fa-2x"></i></a>
								{% endif %}
							</td>
						</tr>
						<!-- Div -->
						<div id="open_{{ category.id }}" class="white-popup-block mfp-hide form-horizontal">
							<div class="form-group mt-lg">
								<label class="col-sm-3 control-label"><strong>Deskripsi:</strong></label>
								<div class="col-sm-9">
									{{ category.description }}
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label"><strong>Meta Title:</strong></label>
								<div class="col-sm-9">
									{{ category.meta_title }}
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label"><strong>Meta Desc:</strong></label>
								<div class="col-sm-9">
									{{ category.meta_desc }}
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label"><strong>Meta Keyword:</strong></label>
								<div class="col-sm-9">
									{{ category.meta_keyword }}
								</div>
							</div>
						</div>
						<!-- Div -->
					{% elsefor %}
						<tr>
							<td colspan="3"><i>Belum ada Category</i></td>
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
							<a href="/admin/product_categories/index/page:{{ i }}{% if product_category_keyword %}?keyword={{ product_category_keyword }}{% endif %}">{{ i }}</a>
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
			if ('delete' === this.className && !confirm('Anda yakin ingin menghapus kategori ini ?')) {
				return !1
			}
			var form = document.createElement('form');
			form.method = 'POST',
			form.action = 'delete' === this.className
			? '/admin/product_categories/delete/' + this.dataset.id
			: '/admin/product_categories/update/' + this.dataset.id + '/published:1?next=' + window.location.href.split('#')[0] + '#' + this.dataset.id,
			document.body.appendChild(form),
			form.submit()
		}
	}
</script>