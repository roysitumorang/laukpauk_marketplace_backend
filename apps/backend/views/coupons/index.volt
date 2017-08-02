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
				<a href="/admin/coupons"><h2>Kupon Member</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span>Kupon</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Kupon</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				{{ flashSession.output() }}
				<p style="margin-left:5px"><i class="fa fa-plus-square"></i>&nbsp;<a href="/admin/coupons/create">Tambah Kupon</a></p>
				<form method="GET" action="/admin/coupons">
					<table class="table table-striped">
						<tr>
							<td>
								<b>Cari berdasarkan :</b>
								<input type="text" name="keyword" value="{{ keyword }}" size="30" placeholder="Kode Kupon">
								<b>Status :</b>
								<select name="status">
									<option value="">All</option>
									{% for key, value in status %}
									<option value="{{ key }}"{% if current_status === key %} selected{% endif %}>{{ value }}</option>
									{% endfor %}
								</select>
								<button type="submit" class="btn btn-info">CARI</button>
							</td>
						</tr>
					</table>
				</form>
				<table class="table table-striped">
					<thead>
						<tr>
							<th width="25"><b>No</b></th>
							<th><b>Kupon</b></th>
							<th><b>Diskon</b></th>
							<th><b>Masa Berlaku</b></th>
							<th><b>#</b></th>
						</tr>
					</thead>
					<tbody>
					{% for coupon in coupons %}
						<tr>
							<td>{{ coupon.rank }}</td>
							<td>
								<font size="4" color="#006bb3"><strong><a href="/admin/coupons/{{ coupon.id }}">{{ coupon.code }}</a></strong></font>&nbsp;
								<img src="/assets/image/bullet-{% if coupon.status == 1 %}green{% else %}red{% endif %}.png" border="0"><br>
								{{ coupon.multiple_use }}
								<br>
								Pemakaian maksimal {{ number_format(coupon.maximum_usage) }} order
								<br>
								Min. Pembelian: Rp. {{ number_format(coupon.minimum_purchase) }}
							</td>
							<td>
								<font size="4">
									{% if coupon.discount_type == 1 %}
									Rp. {{ number_format(coupon.price_discount) }}
									{% else %}
									{{ coupon.price_discount }} %
									{% endif %}
								</font>
							</td>
							<td>{{ coupon.effective_date_start }} s/d {{ coupon.effective_date_end }}</td>
							<td>
								<a href="/admin/coupons/{{ coupon.id }}/update" title="Ubah"><i class="fa fa-pencil-square fa-2x"></i></a>
							</td>
						</tr>
					{% elsefor %}
						<tr>
							<td colspan="5"><i>Belum ada kupon</i></td>
						</tr>
					{% endfor %}
					</tbody>
				</table>
				{% if page.total_pages > 1 %}
				<div class="weepaging">
					<p>
						<b>Halaman:</b>&nbsp;&nbsp;
						{% for i in pages %}
							{% if i == page.current %}
							<b>{{ i }}</b>
							{% else %}
							<a href="/admin/coupons{% if i > 1%}/index/page:{{ i }}{% endif %}">{{ i }}</a>
							{% endif %}
						{% endfor %}
					</p>
				</div>
				{% endif %}
				<!-- eof Content //-->
			</div>
			<!-- end: page -->
		</section>
	</div>
	{{ partial('partials/right_side') }}
</section>
