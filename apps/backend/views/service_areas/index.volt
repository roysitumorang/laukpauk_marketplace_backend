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
				<a href="/admin/users/{{ user.id }}/service_areas"><h2>Area Operasional</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span><a href="/admin/users">Daftar Member</a></span></li>
						<li><span><a href="/admin/users/{{ user.id }}">{{ user.name }}</a></span></li>
						<li><span>Area Operasional</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Area Operasional {{ user.name }}</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				<div class="tabs">
					{{ partial('partials/tabs_user', ['user': user, 'expand': 'areas']) }}
					<div class="tab-content">
						<div id="areas" class="tab-pane active">
							{{ partial('partials/form_service_area', ['action': '/admin/users/' ~ user.id ~ '/service_areas/create', 'user': user, 'pages': pages, 'page': page, 'service_areas': service_areas, 'service_area': service_area, 'provinces': provinces, 'cities': cities, 'subdistricts': subdistricts, 'villages': villages, 'current_cities': current_cities, 'current_subdistricts': current_subdistricts, 'current_villages': current_villages]) }}
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
