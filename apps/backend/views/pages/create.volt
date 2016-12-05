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
				<a href="/admin/pages/create/page_category_id:{{ page_category.id }}{% if parent_id %}/parent_id:{{ parent_id }}{% endif %}"><h2>Tambah Menu Baru</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span><a href="/admin/page_categories">Pages</a></span></li>
						<li><span><a href="/admin/pages/index/page_category_id:{{ page_category.id }}{% if parent_id %}/parent_id:{{ parent_id }}{% endif %}">{{ page_category.name }}</a></span></li>
						<li><span><a href="/admin/pages/create/page_category_id:{{ page_category.id }}{% if parent_id %}/parent_id:{{ parent_id }}{% endif %}">Tambah Menu Baru</a></span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Tambah Menu Baru</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				{{ partial('partials/form_page', ['action': '/admin/pages/create/page_category_id:' ~ page_category.id ~ (parent_id ? '/parent_id:' ~ parent_id : ''), 'page_category': page_category, 'page': page]) }}
				<!-- eof Content //-->
			</div>
			<!-- end: page -->
		</section>
	</div>
	{{ partial('partials/right_side') }}
</section>