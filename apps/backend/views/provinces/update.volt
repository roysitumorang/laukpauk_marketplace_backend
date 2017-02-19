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
				<a href="/admin/provinces/update/{{ province.id }}{% if page.current > 1 %}/page:{{ page.current }}{% endif %}"><h2>Update Propinsi {{ province.name }}</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span><a href="/admin/provinces{% if page.current > 1 %}/index/page:{{ page.current }}{% endif %}">Daftar Propinsi</a></span></li>
						<li><span>Update Propinsi {{ province.name }}</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Daftar Propinsi</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				<div class="tabs">
					{{ partial('partials/tabs_province', ['active_tab': active_tab]) }}
					<div class="tab-content">
						<div id="provinces" class="tab-pane active">
							{{ partial('partials/form_province', ['action': '/admin/provinces/update/' ~ province.id, 'province': province, 'pages': pages, 'page': page, 'provinces': provinces]) }}
						</div>
					</div>
				</div>
				<!-- eof Content //-->
			</div>
			<!-- end: page -->
		</section>
	</div>
	{{ partial('partials/right_side') }}
</section>
