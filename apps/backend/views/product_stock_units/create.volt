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
				<a href="/admin/product_stock_units/create/product_id:{{ product.id }}"><h2>Tambah Satuan Produk {{ product.name }}</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><a href="/admin/products">Produk</a></li>
						<li><a href="/admin/products/update/{{ product.id }}">{{ product.name }}</a></li>
						<li><a href="/admin/product_stock_units/index/product_id:{{ product.id }}">Satuan Produk</a></li>
						<li><span>Tambah Satuan</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Tambah Satuan Produk {{ product.name }}</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				{{ partial('partials/form_product_stock_unit', ['product': product, 'stock_unit': stock_unit, 'action': '/admin/product_stock_units/create/product_id:' ~ product.id]) }}
				<!-- eof Content //-->
			</div>
			<!-- end: page -->
		</section>
	</div>
	{{ partial('partials/right_side') }}
</section>