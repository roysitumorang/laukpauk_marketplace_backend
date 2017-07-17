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
				<a href="/orders/index{% if from %}/from:{{ from }}{% endif %}{% if to %}/to:{{ to }}{% endif %}{% if code %}/code:{{ code }}{% endif %}{% if current_status %}/status:{{ current_status }}{% endif %}{% if page.current > 1 %}/page:{{ page.current }}{% endif %}"><h2>Order List</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/"><i class="fa fa-home"></i></a></li>
						<li><span><a href="/orders/index{% if from %}/from:{{ from }}{% endif %}{% if to %}/to:{{ to }}{% endif %}{% if code %}/code:{{ code }}{% endif %}{% if current_status %}/status:{{ current_status }}{% endif %}{% if page.current > 1 %}/page:{{ page.current }}{% endif %}"">Order List</a></span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Order List</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				{{ flashSession.output() }}
				<form method="GET" action="/orders" id="search">
					<table class="table table-striped">
						<tr>
							<td>
								Dari Tanggal :<br>
								<input type="text" name="from" value="{{ from }}" data-plugin-datepicker data-date-format="yyyy-mm-dd" class="form form-control text-center date" size="10" placeholder="Dari Tanggal">
							</td>
							<td>
								Sampai Tanggal :<br>
								<input type="text" name="to" value="{{ to }}" data-plugin-datepicker data-date-format="yyyy-mm-dd" class="form form-control text-center date" size="10" placeholder="Sampai Tanggal">
							</td>
							<td>
								Nomor Order :<br>
								<input type="text" name="code" value="{{ code }}" class="form form-control text-center" size="6" placeholder="Nomor Order">
							</td>
							<td>
								Status Order :<br>
								<select name="status" class="form form-control">
									<option value="">Any Status</option>
									{% for value, label in status %}
									<option value="{{ value }}"{% if current_status === value %} selected{% endif %}>{{ label }}</option>
									{% endfor %}
								</select>
							</td>
							<td>
								<br>
								<button type="submit" class="btn btn-info">CARI</button>
							</td>
						</tr>
					</table>
				</form>
				<table class="table table-striped">
					<tr>
						<td class="text-center"><b>Total Order : {{ page.total_items }}</b></td>
						<td class="text-center"><b>Total Tagihan : Rp. {{ number_format(total_final_bill) }}</b></td>
						<td class="text-center"><b>Total Biaya Admin : Rp. {{ number_format(total_admin_fee) }}</b></td>
					</tr>
				</table>
				<table class="table table-striped">
					<thead>
						<tr>
							<th class="text-center">No</th>
							<th class="text-center">No. / Tgl Order</th>
							<th class="text-center">Status / Pengantaran</th>
							<th class="text-center">Pembeli</th>
							<th class="text-center">Tagihan / Biaya Admin</th>
							<th class="text-center">#</th>
						</tr>
					</thead>
					<tbody>
					{% for order in orders %}
						<tr id="{{ order.id }}">
							<td class="text-right">{{ order.rank }}</td>
							<td class="text-nowrap" style="background:#{% if order.status == 0 %}FFCCCC{% elseif order.status == 1 %}CCFFCC{% elseif order.status == -1 %}FF0000;color:#FFFFFF{% endif %}">
								<strong>
									#{{ order.code }}<br>
									{{ date('Y-m-d H:i', strtotime(order.created_at)) }}
								</strong>
							</td>
							<td class="text-nowrap">
								<strong>
									{% if order.status == 1 %}
										<i class="fa fa-check-circle"></i> COMPLETED
									{% elseif order.status == -1 %}
										<i class="fa fa-times-circle"></i> CANCELLED
									{% else %}
										<i class="fa fa-paper-plane"></i> HOLD
									{% endif %}
								</strong>
								<br>
								<strong>Jadwal :</strong>
								{{ date('Y-m-d H:i', strtotime(order.scheduled_delivery)) }}<br>
								<strong>Aktual :</strong>
								{% if order.status == 1 %}{{ date('Y-m-d H:i', strtotime(order.actual_delivery)) }}{% else %}-{% endif %}
							</td>
							<td>
								{{ order.name }}<br>
								<i class="fa fa-phone-square"></i>&nbsp;{{ order.mobile_phone }}
							</td>
							<td class="text-right">
								Rp. {{ number_format(order.final_bill) }} / Rp. {{ number_format(order.admin_fee) }}
							</td>
							<td>
								<a href="/orders/{{ order.id }}" title="Detail"><i class="fa fa-info-circle fa-2x"></i></a>
							</td>
						</tr>
					{% elsefor %}
						<tr>
							<td colspan="6"><i>Belum ada order</i></td>
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
							<a href="/orders/index{% if from %}/from:{{ from }}{% endif %}{% if to %}/to:{{ to }}{% endif %}{% if code %}/code:{{ code }}{% endif %}{% if current_status %}/status:{{ current_status }}{% endif %}/page:{{ i }}">{{ i }}</a>
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
	let search = document.getElementById('search'), url = '/orders/index', replacement = {' ': '+', ':': '', '\/': ''};
	search.addEventListener('submit', event => {
		event.preventDefault();
		['from', 'to', 'code', 'status'].forEach(function(attribute) {
			if (search[attribute].value) {
				url += '/' + attribute + ':' + search[attribute].value.trim().replace(/ |:|\//g, match => {
					return replacement[match];
				});
			}
			location.href = url;
		}, false);
	});
</script>