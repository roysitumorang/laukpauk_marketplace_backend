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
				<a href="/products/create"><h2>Tambah Produk</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/"><i class="fa fa-home"></i></a></li>
						<li><span><a href="/products">Daftar Produk</a></span></li>
						<li><span>Tambah Produk</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Tambah Produk</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				{{ partial('partials/form_product', ['action': '/products/create']) }}
				<!-- eof Content //-->
			</div>
			<!-- end: page -->
		</section>
	</div>
	{{ partial('partials/right_side') }}
</section>