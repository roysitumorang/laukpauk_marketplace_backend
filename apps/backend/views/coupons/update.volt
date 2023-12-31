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
				<a href="/admin/coupons/{{ coupon.id }}/update"><h2>Update Kupon #{{ coupon.id }}</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span><a href="/admin/coupons">Kupon</a></span></li>
						<li><span><a href="/admin/coupons/{{ coupon.id }}">Detail Kupon #{{ coupon.id }}</a></span></li>
						<li><span><a href="/admin/coupons/{{ coupon.id }}/update">Update Kupon #{{ coupon.id }}</a></span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Update Kupon #{{ coupon.id }}</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				{{ partial('partials/form_coupon', ['action': '/admin/coupons/' ~ coupon.id ~ '/update', 'discount_types': discount_types, 'status': status, 'usage_types': usage_types]) }}
				<!-- eof Content //-->
			</div>
			<!-- end: page -->
		</section>
	</div>
	{{ partial('partials/right_side') }}
</section>
