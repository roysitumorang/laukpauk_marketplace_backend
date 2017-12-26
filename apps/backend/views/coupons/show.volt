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
				<a href="/admin/coupons/{{ coupon.id }}"><h2>Detail Kupon #{{ coupon.id }}</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span><a href="/admin/coupons">Kupon</a></span></li>
						<li><span>Detail Kupon #{{ coupon.id }}</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">
					Kode Kupon: <strong>{{ coupon.code }}</strong>&nbsp;
					<img src="/assets/image/bullet-{% if coupon.status == 1 %}green{% else %}red{% endif %}.png" border="0">
				</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				{{ flashSession.output() }}
				{{ partial('partials/tabs_coupon', ['coupon': coupon, 'expand': 'detail']) }}
				<table class="table table-striped">
					<thead>
						<tr>
							<th><b>Discount</b></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>
								<font size="6">
								{% if coupon.discount_type == 1 %}
									Rp. {{ coupon.price_discount | number_format }}
								{% else %}
									{{ coupon.price_discount }} %
								{% endif %}
								</font>
							</td>
						</tr>
					</tbody>
				</table>
				<table class="table table-striped">
					<thead>
						<tr>
							<th><b>Minimum Pembelian</b></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>Rp. {{ coupon.minimum_purchase | number_format }}</td>
						</tr>
					</tbody>
				</table>
				<table class="table table-striped">
					<thead>
						<tr>
							<th><b>Masa Berlaku</b></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>{{ coupon.effective_date_start }} s/d {{ coupon.effective_date_end }}</td>
						</tr>
					</tbody>
				</table>
				<table class="table table-striped">
					<thead>
						<tr>
							<th><b>Cara Penggunaan</b></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>{{ coupon.usage_type }}</td>
						</tr>
					</tbody>
				</table>
				<table class="table table-striped">
					<thead>
						<tr>
							<th><b>Pemakaian Maksimal</b></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>{{ coupon.maximum_usage }} order</td>
						</tr>
					</tbody>
				</table>
				<table class="table table-striped">
					<thead>
						<tr>
							<th><b>Deskripsi</b></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>{{ coupon.description | orElse('-') }}</td>
						</tr>
					</tbody>
				</table>
				<!-- eof Content //-->
			</div>
			<!-- end: page -->
		</section>
	</div>
	{{ partial('partials/right_side') }}
</section>
