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
				<a href="/admin/settings/create{% if page.current > 1 %}/page:{{ page.current }}{% endif %}"><h2>Update Setting</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span><a href="/admin/settings{% if page.current > 1 %}/index/page:{{ page.current }}{% endif %}">Daftar Setting</a></span></li>
						<li><span>Update Setting</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Update Setting</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				<p style="margin-left:5px"><i class="fa fa-plus-square"></i>&nbsp;<a href="/admin/settings/create{% if page.current > 1 %}/page:{{ page.current }}{% endif %}" class="new">Tambah Setting</a></p>
				{{ partial('partials/form_setting', ['action': '/admin/settings/update/' ~ setting.id, 'setting': setting, 'pages': pages, 'page': page, 'settings': settings]) }}
				<!-- eof Content //-->
			</div>
			<!-- end: page -->
		</section>
	</div>
	{{ partial('partials/right_side') }}
</section>