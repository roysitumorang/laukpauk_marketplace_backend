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
				<a href="/admin/orders/{{ order.id }}"><h2>Detail Order #{{ order.code }}</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span><a href="/admin/orders">Order List</a></span></li>
						<li><span><a href="/admin/orders{{ order.id }}">Detail Order #{{ order.code }}</a></span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Detail Order #{{ order.code }}</h2>
				{% if order.status === 'COMPLETED' %}
				<span style="float:right;margin-top:-20px">
					<a href="javascript:void(0)" onclick="let popup=window.open('/admin/orders/{{ order.id }}/print','popup','height=800,width=800,resizeable=no,scrollbars=yes,toolbar=no,status=no');window.focus&&popup.focus()" title="Print Invoice"><i class="fa fa-print fa-2x"></i></a>
				</span>
				{% endif %}
			</header>
			<div class="panel-body">
				<!-- Content //-->
				{{ flashSession.output() }}
				<table class="table table-striped">
					<tr>
						<td style="background:#{% if order.status == 'COMPLETED' %}CCFFCC{% elseif order.status == 'CANCELLED' %}E6B800{% else %}FFCCCC{% endif %}" colspan="2" width="40%">
							<b>Status Order</b>
							<br>
							<font size="4">
								<strong>
									<i class="fa fa-{% if order.status == 'COMPLETED' %}check-circle{% elseif order.status == 'CANCELLED' %}times-circle{% else %}paper-plane{% endif %}"></i>
									{{ order.status }}
								</strong>
							</font>
						</td>
						{% if order.status === 'HOLD' %}
						<td class="text-right">
							<form method="POST" action="/admin/orders/{{ order.id }}/complete">
								<button type="submit" class="complete btn btn-primary"><i class="fa fa-check"></i> COMPLETE</button>
							</form>
						</td>
						<td>
							<form method="POST" action="/admin/orders/{{ order.id }}/cancel">
								<input type="text" name="cancellation_reason" placeholder="Alasan pembatalan">
								<button type="submit" class="cancel btn btn-danger"><i class="fa fa-remove"></i> CANCEL</button>
							</form>
						</td>
						{% else %}
						<td colspan="2"></td>
						{% endif %}
					</tr>
					<tr>
						<td bgcolor="#e0ebeb" colspan="2">
							<b><font color="#000099">Total Tagihan</font></b>
							<br>
							<font size="6">
								<strong>
								{% if order.coupon %}
									<span style="text-decoration:line-through">Rp. {{ number_format(order.original_bill) }}</span>&nbsp;&nbsp;
									Rp. {{ number_format(order.final_bill) }}
								{% else %}
									Rp. {{ number_format(order.original_bill) }}
								{% endif %}
								</strong>
							</font>
						</td>
						<td bgcolor="#E0EBEB" colspan="2">
							<b><font color="#000099">Biaya Admin</font></b>
							<br>
							Rp. {{ number_format(order.admin_fee) }}
						</td>
					</tr>
					<tr>
						<td>
							<b><font color="#000099">Pembeli :</font></b>
						</td>
						<td>
							<b><font color="#000099">Nama</font></b>
							<br>
							{{ order.name }}
						</td>
						<td colspan="2">
							<b><font color="#000099">Nomor HP</font></b>
							<br>
							{{ order.mobile_phone }}
						</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td colspan="3">
							<b><font color="#000099">Alamat</font></b>
							<br>
							{{ order.address }}
						</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>
							<b><font color="#000099">Kelurahan</font></b>
							<br>
							{{ village.name }}
						</td>
						<td colspan="2">
							<b><font color="#000099">Kecamatan</font></b>
							<br>
							{{ village.subdistrict.name }}
						</td>
					</tr>
					<tr>
						<td>
							<b><font color="#000099">Penjual :</font></b>
						</td>
						<td>
							<b><font color="#000099">Nama</font></b>
							<br>
							{{ order.merchant.name }}
						</td>
						<td colspan="2">
							<b><font color="#000099">Nama Toko</font></b>
							<br>
							{{ order.merchant.company }}
						</td>
					</tr>
					<tr>
						<td>
							<b><font color="#000099">Tgl. Order</font></b>
							<br>
							{{ date('Y-m-d H:i', strtotime(order.created_at)) }}
						</td>
						<td>
							<b><font color="#000099">Jadwal Pengantaran</font></b>
							<br>
							{{ date('Y-m-d H:i', strtotime(order.scheduled_delivery)) }}
						</td>
						<td colspan="2">
							<b><font color="#000099">Aktual Pengantaran</font></b>
							<br>
							{% if order.status == 'COMPLETED' %}
							{{ date('Y-m-d H:i', strtotime(order.actual_delivery)) }}
							{% else %}
							-
							{% endif %}
						</td>
					</tr>
					<tr>
						<td colspan="4">
							<b><font color="#000099">Order Items</font></b>
						</td>
					</tr>
					{% for item in order.items %}
					<tr>
						<td colspan="2">
							<b>{{ item.name }}</b><br>
							{{ item.quantity }} x Rp. {{ number_format(item.unit_price) }} @ {{ item.stock_unit }}
						</td>
						<td colspan="2"><b>Rp. {{ number_format(item.quantity * item.unit_price) }}</b></td>
					</tr>
					{% endfor %}
					<tr>
						<td colspan="4">
							<b><font color="#000099">Catatan Tambahan</font></b><br>
							{{ order.note | default('-') }}
						</td>
					</tr>
				</table>
				<!-- eof Content //-->
			</div>
			<!-- end: page -->
		</section>
	</div>
	{{ partial('partials/right_side') }}
</section>
