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
				<a href="/admin/banner_categories/create"><h2>Update Banner Slot: {{ banner_category.name }}</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span><a href="/admin/banner_categories">Banner Slot</a></span></li>
						<li><span><a href="/admin/banner_categories/update/{{ banner_category.id }}">Update Banner Slot: {{ banner_category.name }}</a></span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Update Banner Slot: {{ banner_category.name }}</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				{{ partial('partials/form_banner_category', ['action': '/admin/banner_categories/update/' ~ banner_category.id, 'banner_category': banner_category]) }}
				<!-- eof Content //-->
			</div>
			<!-- end: page -->
		</section>
	</div>
	{{ partial('partials/right_side') }}
</section>