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
				<a href="/admin/cities/create/province_id:{{ province.id }}{% if pagination.current > 1 %}/page:{{ pagination.current }}{% endif %}"><h2>Update Kabupaten / Kota</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span><a href="/admin/provinces">Daftar Propinsi</a></span></li>
						<li><span>{{ province.name }}</span></li>
						<li><span>Update Kabupaten / Kota</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Update Kabupaten / Kota di Propinsi {{ province.name }}</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				<div class="tabs">
					{{ partial('partials/tabs_province', ['active_tab': active_tab, 'province': province]) }}
					<div class="tab-content">
						<div id="cities" class="tab-pane active">
							{{ partial('partials/form_city', ['action': '/admin/cities/update/' ~ city.id ~ '/province_id:' ~ province.id, 'pages': pages, 'pagination': pagination, 'province': province, 'cities': cities, 'types': types]) }}
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