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
				<a href="/admin/users/update/{{ user.id }}"><h2>Tambah Produk</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span><a href="/admin/users">Member List</a></span></li>
						<li><span><a href="/admin/users/update/{{ user.id }}">{{ user.name }}</a></span></li>
						<li><span><a href="/admin/product_prices/index/user_id:{{ user.id }}">Produk</a></span></li>
						<li><span><a href="/admin/product_prices/create/user_id:{{ user.id }}">Tambah Produk</a></span></li>
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
				{{ flashSession.output() }}
				<div class="tabs">
					{{ partial('partials/user_tabs', ['user': user, 'expand': 'products']) }}
					<div class="tab-content">
						<div class="tab-pane active">
							{{ partial('partials/form_product_price', ['action': '/admin/product_prices/create/user_id:' ~ user.id]) }}
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