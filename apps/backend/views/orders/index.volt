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
								<b>Cari berdasarkan :</b>
							</td>
							<td>
								<input type="text" name="code" value="{{ code }}" class="form form-control" size="6" placeholder="Nomor Order">
							</td>
							<td>
								Status Order:&nbsp;&nbsp;
							</td>
							<td>
								<select name="status" class="form form-control">
									<option value="">Any Status</option>
									{% for value, label in status %}
									<option value="{{ value }}"{% if current_status == value %} selected{% endif %}>{{ label }}</option>
									{% endfor %}
								</select>
							</td>
							<td>
								<button type="submit" class="btn btn-info">CARI</button>
							</td>
						</tr>
					</table>
				</form>
				<table class="table table-striped">
					<thead>
						<tr>
							<th width="25"><b>No</b></th>
							<th><b>No. Order</b></th>
							<th><b>Tgl Order</b></th>
							<th><b>Pembeli</b></th>
							<th><b>Pembayaran</b></th>
							<th><b>#</b></th>
						</tr>
					</thead>
					<tbody>
					{% for order in orders %}
						<tr id="{{ order.id }}">
							<td>{{ order.rank }}</td>
							<td{% if order.status == 'HOLD' %} style="background:#FFCCCC"{% elseif order.status == 'COMPLETED' %} style="background:#CCFFCC"{% elseif order.status == 'CANCELLED' %} style="background:#FF0000;color:#FFFFFF"{% endif %}>
								<font size="3">{{ order.code }}</font><br><br><strong>{{ order.status }}</strong>
								<br>Estimated delivery: {{ order.estimated_delivery }}
								{% if order.status == 'COMPLETED' %}
								<br>Actual delivery: {{ order.actual_delivery }}
								{% endif %}
							</td>
							<td>{{ order.created_at }}</td>
							<td>
								<font size="5">{{ order.name }}</font> ({{ order.buyer.name }})<br><br>
								<i class="fa fa-phone-square"></i>&nbsp;{{ order.phone }}
							</td>
							<td>
								<font size="4">Rp. {{ number_format(order.final_bill) }}</font><br><br>
							</td>
							<td>
								<a href="/admin/orders/show/{{ order.id }}" title="Detail"><i class="fa fa-info-circle fa-2x"></i></a><br>
								{% if order.status == 'HOLD' %}
								<a href="/admin/orders/update/{{ order.id }}" title="Ubah"><i class="fa fa-pencil-square fa-2x"></i></a><br>
								<a href="javascript:void(0)" data-id="{{ order.id }}" class="cancel" title="Hapus"><i class="fa fa-trash-o fa-2x"></i></a>
								{% endif %}
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