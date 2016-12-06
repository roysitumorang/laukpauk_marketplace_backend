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
				<a href="/admin/post_categories/update/{{ post_category.id }}"><h2>Update Content Category</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span><a href="/admin/post_categories">Content Category</a></span></li>
						<li><span><a href="/admin/post_categories/update/{{ post_category.id }}">Update Content Category</a></span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Update Content Category: {{ post_category.name }}</h2>
			</header>
			<div class="panel-body">
				{{ partial('partials/form_post_category', ['action': '/admin/post_categories/update/' ~ post_category.id, 'post_category': post_category]) }}
			</div>
			<!-- end: page -->
		</section>
	</div>
	{{ partial('partials/right_side') }}
</section>