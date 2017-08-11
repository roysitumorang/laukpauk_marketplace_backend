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
						<td style="background:#{% if order.status == 'COMPLETED' %}CCFFCC{% elseif order.status == 'CANCELLED' %}E6B800{% else %}FFCCCC{% endif %}">
							<b><font color="#000099"><i class="fa fa-tag"></i> Status</font></b>
							<br>
							<strong>
								<i class="fa fa-{% if order.status == 'COMPLETED' %}check-circle{% elseif order.status == 'CANCELLED' %}times-circle{% else %}paper-plane{% endif %}"></i>
								{{ order.status }}
							</strong>
						</td>
						{% if order.status === 'HOLD' %}
						<td class="text-right">
							<form method="POST" action="/admin/orders/{{ order.id }}/complete">
								<button type="submit" class="complete btn btn-primary"><i class="fa fa-check"></i> COMPLETE</button>
							</form>
						</td>
						<td colspan="2">
							<form method="POST" action="/admin/orders/{{ order.id }}/cancel">
								<input type="text" name="cancellation_reason" placeholder="Alasan pembatalan">
								<button type="submit" class="cancel btn btn-danger"><i class="fa fa-remove"></i> CANCEL</button>
							</form>
						</td>
						{% else %}
						<td colspan="3"></td>
						{% endif %}
					</tr>
					<tr>
						<td bgcolor="#e0ebeb" colspan="2">
							<b><font color="#000099"><i class="fa fa-shopping-bag"></i> Total</font></b>
							<br>
							<strong>
							{% if order.coupon %}
								<span style="text-decoration:line-through">Rp. {{ number_format(order.original_bill) }}</span>&nbsp;&nbsp;
								Rp. {{ number_format(order.final_bill) }}
							{% else %}
								Rp. {{ number_format(order.original_bill) }}
							{% endif %}
							</strong>
						</td>
						<td bgcolor="#E0EBEB" colspan="2">
							<b><font color="#000099"><i class="fa fa-check-square"></i> Biaya Admin</font></b>
							<br>
							Rp. {{ number_format(order.admin_fee) }}
						</td>
					</tr>
					<tr>
						<td style="width:25%">
							<b><font color="#000099"><i class="fa fa-id-badge"></i> Pembeli :</font></b>
						</td>
						<td style="width:25%">
							<b><font color="#000099"><i class="fa fa-user"></i> Nama</font></b>
							<br>
							{{ order.name }}
						</td>
						<td style="width:25%">
							<b><font color="#000099"><i class="fa fa-mobile"></i> Nomor HP</font></b>
							<br>
							{{ order.mobile_phone }}
						</td>
						<td style="width:25%">
							<b><font color="#000099"><i class="fa fa-map-marker"></i> Alamat</font></b>
							<br>
							{{ order.address }}, Kelurahan {{ village.name }}, Kecamatan {{ village.subdistrict.name }}
						</td>
					</tr>
					<tr>
						<td>
							<b><font color="#000099"><i class="fa fa-id-badge"></i> Penjual :</font></b>
						</td>
						<td>
							<b><font color="#000099"><i class="fa fa-user"></i> Nama</font></b>
							<br>
							{{ order.merchant.name }}
							<br>
							({{ order.merchant.company }})
						</td>
						<td>
							<b><font color="#000099"><i class="fa fa-mobile"></i> Nomor HP</font></b>
							<br>
							{{ order.mobile_phone }}
						</td>
						<td>
							<b><font color="#000099"><i class="fa fa-map-marker"></i> Alamat</font></b>
							<br>
							{{ order.merchant.address }}
						</td>
					</tr>
					<tr>
						<td>
							<b><font color="#000099"><i class="fa fa-calendar"></i> Tanggal Order</font></b>
							<br>
							{{ date('Y-m-d H:i', strtotime(order.created_at)) }}
						</td>
						<td>
							<b><font color="#000099"><i class="fa fa-calendar-o"></i> Jadwal Pengantaran</font></b>
							<br>
							{{ date('Y-m-d H:i', strtotime(order.scheduled_delivery)) }}
						</td>
						<td colspan="2">
							<b><font color="#000099"><i class="fa fa-calendar-check-o"></i> Aktual Pengantaran</font></b>
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
							<b><font color="#000099"><i class="fa fa-comment"></i> Catatan Tambahan</font></b><br>
							{{ order.note | default('-') }}
						</td>
					</tr>
					<tr>
						<td colspan="4">
							<b><font color="#000099"><i class="fa fa-shopping-cart"></i> Produk</font></b>
						</td>
					</tr>
					{% for item in order.orderProducts %}
					<tr>
						<td colspan="2">
							<b>{{ item.name }}</b><br>
							{{ item.quantity }} x Rp. {{ number_format(item.price) }} @ {{ item.stock_unit }}
						</td>
						<td colspan="2">Rp. {{ number_format(item.quantity * item.price) }}</td>
					</tr>
					{% endfor %}
					<tr>
						<td>
							<b><font color="#000099"><i class="fa fa-shopping-basket"></i> Subtotal</font></b>
							<br>
							Rp. {{ number_format(order.original_bill) }}
						</td>
						<td>
							<b><font color="#000099"><i class="fa fa-paper-plane"></i> Ongkos Kirim</font></b>
							<br>
							{% if order.shipping_cost %}Rp. {{ number_format(order.shipping_cost) }}{% else %}-{% endif %}
						</td>
						<td>
							<b><font color="#000099"><i class="fa fa-minus-square"></i> Diskon</font></b>
							<br>
							{% if order.discount %}Rp. {{ number_format(order.discount) }}{% else %}-{% endif %}
						</td>
						<td>
							<b><font color="#000099"><i class="fa fa-shopping-bag"></i> Total</font></b>
							<br>
							Rp. {{ number_format(order.final_bill) }}
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
