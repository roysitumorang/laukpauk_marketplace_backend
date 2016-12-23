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
				<a href="order.php"><h2>Order List</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span><a href="/admin/orders">Order List</a></span></li>
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
				<form method="GET" action="/admin/orders">
					<table class="table table-striped">
						<tr>
							<td>
								<br>
								<b>Cari berdasarkan :</b>
							</td>
							<td>
								Dari Tanggal :<br>
								<input type="text" name="from" value="{{ from }}" data-plugin-datepicker data-plugin-options="{format:'yyyy-mm-dd'}" class="form form-control text-center date" size="10" placeholder="Dari Tanggal">
							</td>
							<td>
								Sampai Tanggal :<br>
								<input type="text" name="to" value="{{ to }}" data-plugin-datepicker data-plugin-options="{format:'yyyy-mm-dd'}" class="form form-control text-center date" size="10" placeholder="Sampai Tanggal">
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
									<option value="{{ value }}"{% if current_status == value %} selected{% endif %}>{{ label }}</option>
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
					<thead>
						<tr>
							<th class="text-center"><b>No</b></th>
							<th class="text-center"><b>No. Order</b></th>
							<th class="text-center"><b>Tgl Order</b></th>
							<th class="text-center"><b>Pembeli</b></th>
							<th class="text-center"><b>Supplier</b></th>
							<th class="text-center"><b>Pembayaran</b></th>
							<th class="text-center"><b>#</b></th>
						</tr>
					</thead>
					<tbody>
					{% for order in orders %}
						<tr id="{{ order.id }}">
							<td class="text-right">{{ order.rank }}</td>
							<td{% if order.status == 'HOLD' %} style="background:#FFCCCC"{% elseif order.status == 'COMPLETED' %} style="background:#CCFFCC"{% elseif order.status == 'CANCELLED' %} style="background:#FF0000;color:#FFFFFF"{% endif %}>
								<font size="3">#{{ order.code }}</font><br><strong>{{ order.status }}</strong>
								<br>Pengantaran: {{ date('Y-m-d H:i', strtotime(order.estimated_delivery)) }}
								{% if order.status == 'COMPLETED' %}
								<br>Actual delivery: {{ order.actual_delivery }}
								{% endif %}
							</td>
							<td class="text-center">{{ date('Y-m-d H:i', strtotime(order.created_at)) }}</td>
							<td>
								<font size="5">{{ order.name }}</font><br>
								<i class="fa fa-phone-square"></i>&nbsp;{{ order.mobile_phone }}
							</td>
							<td>
								<font size="5">{% if order.merchant.company %}{{ order.merchant.company }}{% else %}{{ order.merchant.name }}{% endif %}</font><br>
								<i class="fa fa-phone-square"></i>&nbsp;{{ order.merchant.mobile_phone }}
							<td class="text-right">
								<font size="4">Rp. {{ number_format(order.final_bill) }}</font><br><br>
							</td>
							<td>
								<a href="/admin/orders/show/{{ order.id }}" title="Detail"><i class="fa fa-info-circle fa-2x"></i></a>
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
							<a href="/admin/orders/index/page:{{ i }}">{{ i }}</a>
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