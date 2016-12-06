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
				<a href="/admin/banners/index/banner_category_id:{{ banner_category.id }}"><h2>Banner: <font color="#FF6600">{{ banner_category.name }}</font></h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span><a href="/admin/banner_categories">Banner Slot</a></span></li>
						<li><span><a href="/admin/banners/index/page_category_id:{{ banner_category.id }}">{{ banner_category.name }}</a></span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">{{ banner_category.name }}</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				{{ flashSession.output() }}
				<a href="/admin/banners/create/banner_category_id:{{ banner_category.id }}" title="Tambah Banner Baru"><i class="fa fa-plus-square"></i>&nbsp;<font size="2">Tambah Banner Baru</font></a>
				<br><br>
				<table class="table table-striped">
				{% for banner in banners %}
					{% if banner.published %}
						{% set background = '' %}
					{% else %}
						{% set background = ' style="opacity:0.4;filter:alpha(opacity=40)"' %}
					{% endif %}
					<tr>
						<td{{ background }}>
							{% if banner.file_name %}
							<a class="image-popup-no-margins" href="/assets/images/{{ banner.file_name }}"><img src="/assets/images/{{ banner.thumbnail }}" border="0"></a>
							{% else %}
							<img src="/assets/images/no_banner_800.png" border="0">
							{% endif %}
							<br>
							<b><font size="4">{{ banner.name }}</font></b><br>
							{% if banner.file_url %}
							<a href="{{ banner.file_url }}" target="_blank">{{ banner.file_url }}</a>
							{% else %}
							------
							{% endif %}
						</td>
						<td{{ background}} width="5%">
							<a href="javascript:void(0)" class="published" data-banner-category-id="{{ banner_category.id }}" data-id="{{ banner.id }}">
								<img src="/assets/images/bullet-{% if banner.published %}green{% else %}red{% endif %}.png" border="0">
							</a>
							<br><br><br>
							<a href="/admin/banners/update/{{ banner.id }}/banner_category_id:{{ banner_category.id  }}" title="Ubah"><i class="fa fa-pencil-square fa-2x"></i></a><br>
							<a href="javascript:void(0)" class="delete" data-banner-category-id="{{ banner_category.id }}" data-id="{{ banner.id }}" title="Hapus">
								<i class="fa fa-trash-o fa-2x"></i>
							</a>
						</td>
					</tr>
				{% endfor %}
				</table>
				{% if page.total_pages > 1 %}
				<div class="weepaging">
					<p>
						<b>Halaman:</b>&nbsp;&nbsp;
						{% for i in pages %}
							{% if i == page.current %}
							<b>{{ i }}</b>
							{% else %}
							<a href="/admin/banners/index/banner_category_id:{{ banner_category.id }}/page:{{ i }}">{{ i }}</a>
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
			if (!confirm('Anda yakin ingin menghapus banner ini ?')) {
				return !1
			}
			var form = document.createElement('form');
			form.method = 'POST',
			form.action = '/admin/banners/delete/' + this.dataset.id + '/banner_category_id:' + this.dataset.bannerCategoryId,
			document.body.appendChild(form),
			form.submit()
		}
	}
	for (var items = document.querySelectorAll('.published'), i = items.length; i--; ) {
		items[i].onclick = function() {
			var form = document.createElement('form');
			form.method = 'POST',
			form.action = '/admin/banners/update/' + this.dataset.id + '/banner_category_id:' + this.dataset.bannerCategoryId + '/published:1',
			document.body.appendChild(form),
			form.submit()
		}
	}
</script>