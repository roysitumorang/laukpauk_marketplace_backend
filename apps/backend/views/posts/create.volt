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
				<a href="/admin/posts/create/post_category_id:{{ post_category.id }}"><h2>Tambah Content</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span><a href="/admin/post_categories">Content</a></span></li>
						<li><span><a href="/admin/posts/index/post_category_id:{{ post_category.id }}">{{ post_category.name }}</a></span></li>
						<li><span><a href="/admin/posts/create/post_category_id:{{ post_category.id }}">Tambah Content</a></span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Tambah Content</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				{{ partial('partials/form_post', ['action': '/admin/posts/create/post_category_id:' ~ post_category.id, 'post_category': post_category, 'post': post]) }}
				<!-- eof Content //-->
			</div>
			<!-- end: page -->
		</section>
	</div>
	{{ partial('partials/right_side') }}
</section>