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
								<span style="text-decoration:line-through">Rp. {{ order.original_bill | number_format }}</span>&nbsp;&nbsp;
								Rp. {{ order.final_bill | number_format }}
							{% else %}
								Rp. {{ order.original_bill | number_format }}
							{% endif %}
							</strong>
						</td>
						<td bgcolor="#E0EBEB" colspan="2">
							<b><font color="#000099"><i class="fa fa-check-square"></i> Biaya Admin</font></b>
							<br>
							Rp. {{ order.admin_fee | number_format }}
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
							{{ order.created_at | datetime }}
						</td>
						<td>
							<b><font color="#000099"><i class="fa fa-calendar-o"></i> Jadwal Pengantaran</font></b>
							<br>
							{{ order.scheduled_delivery | datetime }}
						</td>
						<td colspan="2">
							<b><font color="#000099"><i class="fa fa-calendar-check-o"></i> Aktual Pengantaran</font></b>
							<br>
							{% if order.status == 'COMPLETED' %}
							{{ order.actual_delivery | datetime }}
							{% else %}
							-
							{% endif %}
						</td>
					</tr>
					<tr>
						<td colspan="4">
							<b><font color="#000099"><i class="fa fa-comment"></i> Catatan Tambahan</font></b><br>
							{{ order.note | orElse('-') }}
						</td>
					</tr>
					<tr>
						<td colspan="4">
							<b><font color="#000099"><i class="fa fa-shopping-cart"></i> Produk</font></b>
						</td>
					</tr>
					{% for item in order.getRelated('orderProducts', ['parent_id IS NULL']) %}
					<tr>
						<td colspan="2">
							<b>{{ item.name }}</b><br>
							{{ item.quantity }} x Rp. {{ item.price | number_format }} @ {{ item.stock_unit }}
						</td>
						<td colspan="2">Rp. {{ (item.quantity * item.price) | number_format }}</td>
					</tr>
					{% endfor %}
					<tr>
						<td>
							<b><font color="#000099"><i class="fa fa-shopping-basket"></i> Subtotal</font></b>
							<br>
							Rp. {{ order.original_bill | number_format }}
						</td>
						<td>
							<b><font color="#000099"><i class="fa fa-paper-plane"></i> Ongkos Kirim</font></b>
							<br>
							{% if order.shipping_cost %}Rp. {{ order.shipping_cost | number_format }}{% else %}-{% endif %}
						</td>
						<td>
							<b><font color="#000099"><i class="fa fa-minus-square"></i> Diskon</font></b>
							<br>
							{% if order.discount %}Rp. {{ order.discount | number_format }}{% else %}-{% endif %}
						</td>
						<td>
							<b><font color="#000099"><i class="fa fa-shopping-bag"></i> Total</font></b>
							<br>
							Rp. {{ order.final_bill | number_format }}
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
