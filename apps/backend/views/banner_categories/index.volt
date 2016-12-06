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
				<a href="/admin/banner_categories"><h2>Banner Slot</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span><a href="/admin/banner_categories">Banner Slot</a></span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Banner Slot</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				{{ flashSession.output() }}
				<p style="margin-left:5px"><i class="fa fa-plus-square"></i>&nbsp;<a href="/admin/banner_categories/create">New Banner Slot</a></p>
				<div class="row">
				{% for banner_category in banner_categories %}
					<div class="col-md-4 panel" style="width:20%">
						<div class="panel-body panel-featured">
							<a href="/admin/banners/index/banner_category_id:{{ banner_category.id }}" title="{{ banner_category.name }}"><i class="fa fa-tags fa-5x"></i></a><br>
							{{ banner_category.name }}<br>
							<a href="/admin/banner_categories/update/{{ banner_category.id }}" title="Ubah Banner Slot"><i class="fa fa-pencil-square fa-2x"></i></a>
							{% if !banner.removable %}
							&nbsp;<a href="javascript:void(0)" class="delete" data-id="{{ banner_category.id }}" title="Hapus"><i class="fa fa-trash-o fa-2x"></i></a>
							{% endif %}
						</div>
					</div>
				{% endfor %}
				</div>
				{% if page.total_pages > 1 %}
				<div class="weepaging">
					<p>
						<b>Halaman:</b>&nbsp;&nbsp;
						{% for i in pages %}
							{% if i == page.current %}
							<b>{{ i }}</b>
							{% else %}
							<a href="/admin/banner_categories/page:{{ i }}">{{ i }}</a>
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
	for (var items = document.querySelectorAll('.delete'), i = items.length; i--; ) {
		items[i].onclick = function() {
			if (!confirm('Anda yakin ingin menghapus banner slot ini ?')) {
				return !1
			}
			var form = document.createElement('form');
			form.method = 'POST',
			form.action = '/admin/banner_categories/delete/' + this.dataset.id,
			document.body.appendChild(form),
			form.submit()
		}
	}
</script>