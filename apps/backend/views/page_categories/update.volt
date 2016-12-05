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
				<a href="/admin/page_categories"><h2>Menu Directory</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span><a href="/admin/page_categories">Menu Directory</a></span></li>
						<li><span><a href="/admin/page_categories/update/{{ page_category.id }}">Update</a></span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Menu Directory</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				{{ partial('partials/form_page_category', ['action': '/admin/page_categories/update/' ~ page_category.id, 'page_category': page_category]) }}
			</div>
			<!-- end: page -->
		</section>
	</div>
	{{ partial('partials/right_side') }}
</section>