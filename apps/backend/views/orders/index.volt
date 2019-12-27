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
				<a href="/admin/orders/index{% if from %}/from={{ from }}{% endif %}{% if to %}/to={{ to }}{% endif %}{% if code %}/code={{ code }}{% endif %}{% if current_status %}/status={{ current_status }}{% endif %}{% if mobile_phone %}/mobile_phone={{ mobile_phone }}{% endif %}{% if pagination.current > 1 %}/page={{ pagination.current }}{% endif %}"><h2>Order List</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span><a href="/admin/orders/index{% if from %}/from={{ from }}{% endif %}{% if to %}/to={{ to }}{% endif %}{% if code %}/code={{ code }}{% endif %}{% if current_status %}/status={{ current_status }}{% endif %}{% if mobile_phone %}/mobile_phone={{ mobile_phone }}{% endif %}{% if pagination.current > 1 %}/page={{ pagination.current }}{% endif %}">Order List</a></span></li>
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
				{{ form('/admin/orders/index', 'method': 'GET', 'id': 'search') }}
					<table class="table table-striped">
						<tr>
							<td>
								<i class="fa fa-calendar"></i> Dari Tanggal :<br>
								{{ text_field('from', 'value': from, 'data-plugin-datepicker': true, 'data-date-format': 'yyyy-mm-dd', 'class': 'form form-control text-center date', 'size': '10', 'placeholder': 'Dari Tanggal') }}
							</td>
							<td>
								<i class="fa fa-calendar"></i> Sampai Tanggal :<br>
								{{ text_field('to', 'value': to, 'data-plugin-datepicker': true, 'data-date-format': 'yyyy-mm-dd', 'class': 'form form-control text-center date', 'size': '10', 'placeholder': 'Sampai Tanggal') }}
							</td>
							<td>
								<i class="fa fa-id-badge"></i> Nomor Order :<br>
								{{ text_field('code', 'value': code, 'class': 'form form-control text-center', 'size': 6, 'placeholder': 'Nomor Order') }}
							</td>
							<td>
								<i class="fa fa-tag"></i> Status :<br>
								{{ select_static({'status', order_status, 'value': current_status, 'useEmpty': true, 'emptyText': '- Semua -', 'emptyValue': ''}) }}
							</td>
							<td>
								<i class="fa fa-mobile"></i> HP Merchant :<br>
								{{ text_field('mobile_phone', 'value': mobile_phone, 'class': 'form form-control text-center', 'size': 6, 'placeholder': 'HP Merchant') }}
							</td>
							<td>
								<br>
								<button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> CARI</button>
							</td>
						</tr>
					</table>
				{{ endForm() }}
				<table class="table table-striped">
					<tr>
						<td class="text-center"><b>Total Order : {{ pagination.total_items }}</b></td>
						<td class="text-center"><b>Total Tagihan : Rp. {{ total_final_bill | number_format }}</b></td>
						<td class="text-center"><b>Total Biaya Admin : Rp. {{ total_admin_fee | number_format }}</b></td>
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
						<tr id="{{ order.code }}">
							<td class="text-right">{{ order.rank }}</td>
							<td class="text-nowrap" style="background:#{% if order.status == 0 %}FFCCCC{% elseif order.status == 1 %}CCFFCC{% elseif order.status == -1 %}FF0000;color:#FFFFFF{% endif %}">
								<strong>
									#{{ order.code }}<br>
									<i class="fa fa-calendar"></i> {{ order.created_at | datetime }}
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
								{{ order.scheduled_delivery | datetime }}
								<br>
								<strong><i class="fa fa-calendar-check-o"></i> </strong>
								{% if order.status == 1 %}{{ order.actual_delivery | datetime }}{% else %}-{% endif %}
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
								<i class="fa fa-shopping-bag"></i> Rp. {{ order.final_bill | number_format }}
								<br>
								<i class="fa fa-check-square"></i> {% if order.admin_fee %}Rp. {{ order.admin_fee | number_format }}{% else %}-{% endif %}
							</td>
							<td>
								<a href="/admin/orders/{{ order.code }}" title="Detail"><i class="fa fa-external-link fa-2x"></i></a>
							</td>
						</tr>
					{% elsefor %}
						<tr>
							<td colspan="7"><i>Belum ada order</i></td>
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
								<a href="/admin/orders/index{% if from %}/from={{ from }}{% endif %}{% if to %}/to={{ to }}{% endif %}{% if code %}/code={{ code }}{% endif %}{% if current_status %}/status={{ current_status }}{% endif %}{% if mobile_phone %}/mobile_phone={{ mobile_phone }}{% endif %}{% if i > 1 %}/page={{ i }}{% endif %}">{{ i }}</a>
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
	document.querySelector('#search').addEventListener('submit', event => {
		let url = event.target.action, replacement = {' ': '+', '=': '', '\/': ''};
		event.preventDefault(),
		['from', 'to', 'code', 'status', 'mobile_phone'].forEach(function(attribute) {
			event.target[attribute].value && (url += '/' + attribute + '=' + event.target[attribute].value.trim().replace(/ |=|\//g, match => {
				return replacement[match]
			}));
		}),
		location.href = url
	}, false)
</script>
