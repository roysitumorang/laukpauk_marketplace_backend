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
				<a href="/admin/groups/{{ group.id }}/update"><h2>Edit Group Produk</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span><a href="/admin/groups">Group Produk</a></span></li>
						<li><span>Edit</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Edit Group Produk</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				<div class="tabs">
					{{ partial('partials/tabs_group', ['expand': 'group']) }}
					<div class="tab-content">
						<div id="groups" class="tab-pane active">
							{{ partial('partials/form_group', ['action': '/admin/groups/update/' ~ group.id, 'expand': group]) }}
						</div>
					</div>
				</div>
			</div>
			<!-- end: page -->
		</section>
	</div>
	{{ partial('partials/right_side') }}
</section>