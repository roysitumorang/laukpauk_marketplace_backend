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
				{{ form('/admin/coupons/index', 'method': 'GET', 'id': 'search') }}
					<table class="table table-striped">
						<tr>
							<td>
								<b>Cari berdasarkan :</b>
								{{ text_field('keyword', 'value': keyword, 'size': 30, 'placeholder': 'Kode Kupon') }}
								<b>Status :</b>
								{{ select({'status', coupon_status, 'value': current_status, 'useEmpty': true, 'emptyText': '- semua -', 'emptyValue': ''}) }}
								<button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> CARI</button>
								<a type="button" href="/admin/coupons/create" class="btn btn-primary"><i class="fa fa-plus-square"></i> Tambah Kupon</a>
							</td>
						</tr>
					</table>
				{{ endForm() }}
				<table class="table table-striped">
					<thead>
						<tr>
							<th width="25">No</th>
							<th>Kode</th>
							<th>Diskon</th>
							<th>Masa Berlaku</th>
							<th>Pemakaian</th>
							<th colspan="2">#</th>
						</tr>
					</thead>
					<tbody>
					{% for coupon in coupons %}
						<tr>
							<td>{{ coupon.rank }}</td>
							<td>
								<font color="#006bb3"><strong><a href="/admin/coupons/{{ coupon.id }}"{% if coupon.expiry_date <= current_date %} style="text-decoration:line-through"{% endif %}>{{ coupon.code }}</a></strong></font>
								<br>
								{{ coupon.multiple_use }}
								<br>
								Pemakaian Maksimal : {{ coupon.maximum_usage | number_format }} order
								<br>
								Pembelian Minimal : Rp. {{ coupon.minimum_purchase | number_format }}
								{% if coupon.minimum_version %}
								<br>
								Versi Aplikasi Minimal : {{ coupon.minimum_version }}
								{% endif %}
							</td>
							<td>
								{% if coupon.discount_type == 1 %}
									Rp. {{ coupon.price_discount | number_format }}
								{% else %}
									{{ coupon.price_discount }} %
								{% endif %}
							</td>
							<td>{{ coupon.effective_date_start }} - {{ coupon.effective_date_end }}</td>
							<td>{% if coupon.total_usage %}{{ coupon.total_usage | number_format }}{% else %}-{% endif %}</td>
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
							<td colspan="7"><i>Belum ada kupon</i></td>
						</tr>
					{% endfor %}
					</tbody>
				</table>
				{% if pagination.last > 1 %}
				<div class="weepaging">
					<p>
						<b>Halaman:</b>&nbsp;&nbsp;
						{% for i in pages %}
							{% if i == pagination.current %}
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
		link.addEventListener('click', event => {
			let form = document.createElement('form'), input = document.createElement('input');
			event.preventDefault(),
			form.method = 'POST',
			form.action = '/admin/coupons/' + event.target.parentNode.dataset.id + '/toggle_status',
			document.body.appendChild(form),
			form.submit()
		}, false)
	}),
	document.querySelector('#search').addEventListener('submit', event => {
		let url = event.target.action, replacement = {' ': '+', ':': '', '\/': ''};
		event.preventDefault(),
		event.target.keyword.value && (url += '/keyword:' + event.target.keyword.value.trim().replace(/ |:|\//g, match => {
			return replacement[match]
		})),
		event.target.status.value && (url += '/status:' + event.target.status.value),
		location.href = url
	}, false)
</script>