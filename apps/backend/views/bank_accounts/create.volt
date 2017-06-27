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
				<a href="/admin/provinces/create{% if page.current > 1 %}/page:{{ page.current }}{% endif %}"><h2>Tambah Rekening Bank</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span><a href="/admin/provinces{% if page.current > 1 %}/index/page:{{ page.current }}{% endif %}">Daftar Rekening Bank</a></span></li>
						<li><span>Tambah Rekening Bank</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Tambah Propinsi</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				{{ partial('partials/form_bank_account', ['action': '/admin/bank_accounts/create', 'bank_account': bank_account]) }}
				<!-- eof Content //-->
			</div>
			<!-- end: page -->
		</section>
	</div>
	{{ partial('partials/right_side') }}
</section>
