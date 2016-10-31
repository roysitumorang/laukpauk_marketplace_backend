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
				<a href="/admin/brands"><h2>Brand Produk</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span>Brand Produk</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Brand Produk</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				{{ flashSession.output() }}
				<p style="margin-left:5px"><i class="fa fa-plus-square"></i>&nbsp;<a href="/admin/brands/create">New Brand</a></p>
				<table class="table table-striped">
					<thead>
						<tr>
							<th width="1%"><b><font color="#000000">No</font></b></th>
							<th><b><font color="#000000">Nama Brand</font></b></th>
							<th><b><font color="#000000">Permalink</font></b></th>
							<th><b><font color="#000000">#</font></b></th>
						</tr>
					</thead>
					<tbody>
						{% for brand in brands %}
						<tr>
							<td>{{ brand.rank }}</td>
							<td>
								<strong><font size="5"><a href="/admin/products/index/brand_id:{{ brand.id }}" title="{{ brand.name }}">{{ brand.name }}</a></font></strong>
								{% if brand.picture %}
								<br>
								<a class="image-popup-no-margins" href="/assets/images/{{ brand.picture }}">
								<img src="/assets/images/{{ brand.thumbnail }}" border="0"></a>
								{% endif %}
								<br>Total Produk:&nbsp;{{ brand.total_products }}
							</td>
							<td>{{ brand.permalink }}</td>
							<td>
								<a class="popup-with-form" href="#open_{{ brand.id }}"><i class="fa fa-info-circle fa-2x"></i></a>&nbsp;
								<a href="/admin/brands/update/{{ brand.id }}" title="Ubah"><i class="fa fa-pencil-square fa-2x"></i></a>&nbsp;&nbsp;
								<a href="javascript:void(0)" class="delete" data-id="{{ brand.id }}" title="Hapus"><i class="fa fa-trash-o fa-2x"></i></a>
							</td>
						</tr>
						<!-- Div -->
						<div id="open_{{ brand.id }}" class="white-popup-block mfp-hide form-horizontal">
							<div class="form-group mt-lg">
								<label class="col-sm-3 control-label"><strong>Deskripsi:</strong></label>
								<div class="col-sm-9">
									{{ brand.description }}
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label"><strong>Meta Title:</strong></label>
								<div class="col-sm-9">
									{{ brand.meta_title }}
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label"><strong>Meta Desc:</strong></label>
								<div class="col-sm-9">
									{{ brand.meta_desc }}
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label"><strong>Meta Keyword:</strong></label>
								<div class="col-sm-9">
									{{ brand.meta_keyword }}
								</div>
							</div>
						</div>
						<!-- Div -->
						{% elsefor %}
						<tr>
							<td colspan="4"><i>Belum ada data</i></td>
						</tr>
						{% endfor %}
					</tbody>
				</table>
				<!-- eof Content //-->
			</div>
			<!-- end: page -->
		</section>
	</div>
	{{ partial('partials/right_side') }}
</section>
<script>
	for (var items = document.querySelectorAll('.delete'), i = items.length; i--; ) {
		items[i].onclick = function() {
			if (!confirm('Anda yakin ingin menghapus brand ini ?')) {
				return !1
			}
			var form = document.createElement('form');
			form.method = 'POST',
			form.action = '/admin/brands/delete/' + this.dataset.id,
			document.body.appendChild(form),
			form.submit()
		}
	}
</script>