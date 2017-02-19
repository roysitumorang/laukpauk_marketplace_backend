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
				<a href="/admin/villages/create/subdistrict_id:{{ subdistrict.id }}{% if page.current > 1 %}/page:{{ page.current }}{% endif %}"><h2>Tambah Kelurahan</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span><a href="/admin/provinces">Daftar Propinsi</a></span></li>
						<li><span>{{ subdistrict.city.province.name }}</span></li>
						<li><span><a href="/admin/cities/index/province_id:{{ subdistrict.city.province.id }}">Daftar Kabupaten / Kota</a></span></li>
						<li><span>{{ subdistrict.city.name }}</span></li>
						<li><span><a href="/admin/subdistricts/index/city_id:{{ subdistrict.city.id }}">Daftar Kecamatan</a></span></li>
						<li><span>{{ subdistrict.name }}</span></li>
						<li><span>Tambah Kelurahan</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Tambah Kelurahan di Kecamatan {{ subdistrict.name }}, {{ subdistrict.city.type }} {{ subdistrict.city.name }}, Propinsi {{ subdistrict.city.province.name }}</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				<div class="tabs">
					{{ partial('partials/tabs_province', ['active_tab': active_tab]) }}
					<div class="tab-content">
						<div id="cities" class="tab-pane active">
							{{ partial('partials/form_village', ['action': '/admin/villages/create/subdistrict_id:' ~ subdistrict.id, 'pages': pages, 'page': page, 'subdistrict': subdistrict, 'villages': villages, 'village': village]) }}
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
