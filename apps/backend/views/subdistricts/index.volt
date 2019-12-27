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
				<a href="/admin/subdistricts/index/city_id={{ city.id }}{% if page.current > 1 %}/page={{ page.current }}{% endif %}"><h2>Daftar Kecamatan</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span><a href="/admin/provinces">Daftar Propinsi</a></span></li>
						<li><span>{{ province.name }}</span></li>
						<li><span><a href="/admin/cities/index/province_id={{ province.id }}">Daftar Kabupaten / Kota</a></span></li>
						<li><span>{{ city.name }}</span></li>
						<li><span>Daftar Kecamatan</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Daftar Kecamatan di {{ city.type }} {{ city.name }}, Propinsi {{ province.name }}</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				<div class="tabs">
					{{ partial('partials/tabs_province', ['active_tab': active_tab, 'subdistrict': subdistrict]) }}
					<div class="tab-content">
						<div id="subdistricts" class="tab-pane active">
							{{ flashSession.output() }}
							<p style="margin-left:5px"><i class="fa fa-plus-square"></i>&nbsp;<a href="/admin/subdistricts/create/city_id={{ city.id }}{% if page.current > 1 %}/page={{ page.current }}{% endif %}" class="new">Tambah Kecamatan</a></p>
							{{ partial('partials/list_subdistricts', ['pages': pages, 'page': page, 'city': city, 'subdistricts': subdistricts]) }}
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
