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
				<a href="/admin/orders/index{% if from %}/from:{{ from }}{% endif %}{% if to %}/to:{{ to }}{% endif %}{% if code %}/code:{{ code }}{% endif %}{% if current_status %}/status:{{ current_status }}{% endif %}{% if mobile_phone %}/mobile_phone:{{ mobile_phone }}{% endif %}{% if page.current > 1 %}/page:{{ page.current }}{% endif %}"><h2>Order List</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span><a href="/admin/orders/index{% if from %}/from:{{ from }}{% endif %}{% if to %}/to:{{ to }}{% endif %}{% if code %}/code:{{ code }}{% endif %}{% if current_status %}/status:{{ current_status }}{% endif %}{% if mobile_phone %}/mobile_phone:{{ mobile_phone }}{% endif %}{% if page.current > 1 %}/page:{{ page.current }}{% endif %}"">Order List</a></span></li>
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
				<form method="GET" action="/admin/orders" id="search">
					<table class="table table-striped">
						<tr>
							<td>
								<i class="fa fa-calendar"></i> Dari Tanggal :<br>
								<input type="text" name="from" value="{{ from }}" data-plugin-datepicker data-date-format="yyyy-mm-dd" class="form form-control text-center date" size="10" placeholder="Dari Tanggal">
							</td>
							<td>
								<i class="fa fa-calendar"></i> Sampai Tanggal :<br>
								<input type="text" name="to" value="{{ to }}" data-plugin-datepicker data-date-format="yyyy-mm-dd" class="form form-control text-center date" size="10" placeholder="Sampai Tanggal">
							</td>
							<td>
								<i class="fa fa-id-badge"></i> Nomor Order :<br>
								<input type="text" name="code" value="{{ code }}" class="form form-control text-center" size="6" placeholder="Nomor Order">
							</td>
							<td>
								<i class="fa fa-tag"></i> Status :<br>
								<select name="status" class="form form-control">
									<option value="">Any Status</option>
									{% for value, label in status %}
									<option value="{{ value }}"{% if current_status === value %} selected{% endif %}>{{ label }}</option>
									{% endfor %}
								</select>
							</td>
							<td>
								<i class="fa fa-mobile"></i> HP Merchant :<br>
								<input type="text" name="mobile_phone" value="{{ mobile_phone }}" class="form form-control text-center" size="6" placeholder="HP Merchant">
							</td>
							<td>
								<br>
								<button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> CARI</button>
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
							<th class="text-center">Penjual</th>
							<th class="text-center">Total / Biaya Admin</th>
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
									<i class="fa fa-calendar"></i> {{ date('Y-m-d H:i', strtotime(order.created_at)) }}
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
								<strong><i class="fa fa-calendar-o"></i> </strong>
								{{ date('Y-m-d H:i', strtotime(order.scheduled_delivery)) }}
								<br>
								<strong><i class="fa fa-calendar-check-o"></i> </strong>
								{% if order.status == 1 %}{{ date('Y-m-d H:i', strtotime(order.actual_delivery)) }}{% else %}-{% endif %}
							</td>
							<td>
								<i class="fa fa-user"></i> {{ order.name }}<br>
								<i class="fa fa-mobile"></i>&nbsp;{{ order.buyer_phone }}
							</td>
							<td>
								<i class="fa fa-user"></i> {% if order.merchant_company %}{{ order.merchant_company }}{% else %}{{ order.merchant_name }}{% endif %}<br>
								<i class="fa fa-mobile"></i>&nbsp;{{ order.merchant_phone }}
							</td>
							<td>
								<i class="fa fa-shopping-bag"></i> Rp. {{ number_format(order.final_bill) }}
								<br>
								<i class="fa fa-check-square"></i> {% if order.admin_fee %}Rp. {{ number_format(order.admin_fee) }}{% else %}-{% endif %}
							</td>
							<td>
								<a href="/admin/orders/{{ order.id }}" title="Detail"><i class="fa fa-external-link fa-2x"></i></a>
							</td>
						</tr>
					{% elsefor %}
						<tr>
							<td colspan="7"><i>Belum ada order</i></td>
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
							<a href="/admin/orders/index{% if from %}/from:{{ from }}{% endif %}{% if to %}/to:{{ to }}{% endif %}{% if code %}/code:{{ code }}{% endif %}{% if current_status %}/status:{{ current_status }}{% endif %}{% if mobile_phone %}/mobile_phone:{{ mobile_phone }}{% endif %}/page:{{ i }}">{{ i }}</a>
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
	let search = document.getElementById('search'), url = '/admin/orders/index', replacement = {' ': '+', ':': '', '\/': ''};
	search.addEventListener('submit', event => {
		event.preventDefault();
		['from', 'to', 'code', 'status', 'mobile_phone'].forEach(function(attribute) {
			if (search[attribute].value) {
				url += '/' + attribute + ':' + search[attribute].value.trim().replace(/ |:|\//g, match => {
					return replacement[match];
				});
			}
			location.href = url;
		}, false);
	});
</script>