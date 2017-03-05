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
				<a href="/admin/users/{{ user.id }}/store_items/create"><h2>Tambah Produk</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span><a href="/admin/users">Daftar Member</a></span></li>
						<li><span><a href="/admin/users/{{ user.id }}">{{ user.name }}</a></span></li>
						<li><span>Tambah Produk</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Tambah Produk {{ user.name }}</h2>
			</header>
			<div class="panel-body">
				{{ partial('partials/form_store_item', ['action': '/admin/users/' ~ user.id ~ '/store_items/create', 'user': user, 'pages': pages, 'page': page, 'store_items': store_items, 'store_item': store_item, 'categories': categories, 'products': products, 'current_products': current_products, 'order_closing_hours': order_closing_hours]) }}
			</div>
		</section>
	{{ partial('partials/right_side') }}
</section>