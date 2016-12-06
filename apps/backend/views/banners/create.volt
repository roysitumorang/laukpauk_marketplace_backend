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
				<a href="/admin/banners/create/page_category_id:{{ page_category.id }}"><h2>Tambah Banner</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span><a href="/admin/banner_categories">Banner Slot</a></span></li>
						<li><span><a href="/admin/banners/index/banner_category_id:{{ banner_category.id }}">{{ banner_category.name }}</a></span></li>
						<li><span><a href="/admin/banners/create/banner_category_id:{{ banner_category.id }}">Tambah Banner</a></span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Tambah Banner</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				{{ partial('partials/form_banner', ['action': '/admin/banners/create/banner_category_id:' ~ banner_category.id, 'banner_category': banner_category, 'banner': banner]) }}
				<!-- eof Content //-->
			</div>
			<!-- end: page -->
		</section>
	</div>
	{{ partial('partials/right_side') }}
</section>