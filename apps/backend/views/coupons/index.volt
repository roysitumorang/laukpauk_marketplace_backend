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
				<a href="/admin/coupons"><h2>Kupon</h2></a>
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
								<button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> CARI</button>
							</td>
						</tr>
					</table>
				</form>
				<table class="table table-striped">
					<thead>
						<tr>
							<th width="25">No</th>
							<th>Kode</th>
							<th>Diskon</th>
							<th>Masa Berlaku</th>
							<th colspan="2">#</th>
						</tr>
					</thead>
					<tbody>
					{% for coupon in coupons %}
						<tr>
							<td>{{ coupon.rank }}</td>
							<td>
								<font color="#006bb3"><strong><a href="/admin/coupons/{{ coupon.id }}">{{ coupon.code }}</a></strong></font>
								<br>
								{{ coupon.multiple_use }}
								<br>
								Pemakaian maksimal {{ number_format(coupon.maximum_usage) }} order
								<br>
								Min. Pembelian: Rp. {{ number_format(coupon.minimum_purchase) }}
							</td>
							<td>
								{% if coupon.discount_type == 1 %}
									Rp. {{ number_format(coupon.price_discount) }}
								{% else %}
									{{ coupon.price_discount }} %
								{% endif %}
							</td>
							<td>{{ coupon.effective_date_start }} s/d {{ coupon.effective_date_end }}</td>
							<td>
								{% if coupon.expiry_date > current_date %}<a href="javascript:void(0)" class="status" data-id="{{ coupon.id }}">{% endif %}
									<i class="fa fa-eye{% if !coupon.status or coupon.expiry_date <= current_date %}-slash{% endif %} fa-2x"></i>
								{% if coupon.expiry_date > current_date %}</a>{% endif %}
							</td>
							<td>
								<a href="/admin/coupons/{{ coupon.id }}/update" title="Ubah"><i class="fa fa-pencil-square fa-2x"></i></a>
							</td>
						</tr>
					{% elsefor %}
						<tr>
							<td colspan="6"><i>Belum ada kupon</i></td>
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
							<a href="/admin/coupons/index{% if keyword %}/keyword:{{ keyword }}{% endif %}{% if current_status %}/status:{{ current_status }}{% endif %}{% if i > 1%}/page:{{ i }}{% endif %}">{{ i }}</a>
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
<script>
	document.querySelectorAll('.status').forEach(link => {
		link.onclick = () => {
			let form = document.createElement('form'), input = document.createElement('input');
			form.method = 'POST',
			form.action = '/admin/coupons/' + link.dataset.id + '/toggle_status',
			document.body.appendChild(form),
			form.submit()
		}
	})
</script>