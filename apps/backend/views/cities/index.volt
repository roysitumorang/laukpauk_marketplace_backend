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
				<a href="/admin/cities/index/province_id:{{ province.id }}{% if page.current > 1 %}/page:{{ page.current }}{% endif %}"><h2>Daftar Kabupaten / Kota</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span><a href="/admin/provinces">Daftar Propinsi</a></span></li>
						<li><span>{{ province.name }}</span></li>
						<li><span>Daftar Kabupaten / Kota</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Daftar Kabupaten / Kota di Propinsi {{ province.name }}</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				<div class="tabs">
					{{ partial('partials/tabs_province', ['user': user, 'active_tab': active_tab]) }}
					<div class="tab-content">
						<div id="cities" class="tab-pane active">
							{{ flashSession.output() }}
							<p style="margin-left:5px"><i class="fa fa-plus-square"></i>&nbsp;<a href="/admin/cities/create/province_id:{{ province.id }}{% if page.current > 1 %}/page:{{ page.current }}{% endif %}" class="new">Tambah Kabupaten / Kota</a></p>
							{{ partial('partials/list_cities', ['pages': pages, 'page': page, 'province': province, 'cities': cities]) }}
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
