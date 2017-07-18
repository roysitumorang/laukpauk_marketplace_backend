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
				<a href="/coupons/{{ coupon.id }}"><h2>Detail Kupon #{{ coupon.id }}</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/"><i class="fa fa-home"></i></a></li>
						<li><span><a href="/coupons">Kupon</a></span></li>
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
									Rp. {{ number_format(coupon.discount_amount) }}
								{% else %}
									{{ coupon.discount_amount }} %
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
							<td>Rp. {{ number_format(coupon.minimum_purchase) }}</td>
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
							<th><b>Deskripsi</b></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>{{ coupon.description | default('-') }}</td>
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
