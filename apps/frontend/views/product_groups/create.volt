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
				<a href="/product_groups/create"><h2>Tambah Group Produk</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/"><i class="fa fa-home"></i></a></li>
						<li><span><a href="/product_groups">Group Produk</a></span></li>
						<li><span>Tambah</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Tambah Group Produk</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				<div class="tabs">
					{{ partial('partials/tabs_product_group', ['expand': 'group']) }}
					<div class="tab-content">
						<div id="group" class="tab-pane active">
							{{ partial('partials/form_product_group', ['action': '/product_groups/create', 'expand': group]) }}
						</div>
					</div>
				</div>
			</div>
			<!-- end: page -->
		</section>
	</div>
	{{ partial('partials/right_side') }}
</section>