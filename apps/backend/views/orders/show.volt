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
				<a href="/admin/orders/show/{{ order.id }}"><h2>Detail Order #{{ order.code }}</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span><a href="/admin/orders">Order List</a></span></li>
						<li><span><a href="/admin/orders/show/{{ order.id }}">Detail Order #{{ order.code }}</a></span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Detail Order #{{ order.code }}</h2>
				<span style="float:right;margin-top:-20px">
					<a href="javascript:void(0)" onclick="let popup=window.open('/admin/orders/print/{{ order.id }}','popup','height=800,width=800,resizeable=no,scrollbars=yes,toolbar=no,status=no');window.focus&&popup.focus()" title="Print Invoice"><i class="fa fa-print fa-2x"></i></a>
				</span>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				{{ flashSession.output() }}
				{% if order.status === 1 %}
					{% set background = ' style="background:#CCFFCC"' %}
				{% elseif order.status === -1 %}
					{% set background = ' style="background:#E6B800"' %}
				{% else %}
					{% set background = ' style="background:#FFCCCC"' %}
				{% endif %}
				<table class="table table-striped">
					<tr>
						<td{{ background }} colspan="2" width="40%">
							<b>Status Order</b>
							<br>
							<font size="4"><strong>{{ order.status }}</strong></font>
						</td>
						<td colspan="2">
							{% if order.status === 0 %}
							<a type="button" href="javascript:void(0)" data-id="{{ order.id }}" class="complete btn btn-primary">COMPLETE</a>&nbsp;&nbsp;
							<a type="button" href="javascript:void(0)" data-id="{{ order.id }}" class="cancel btn btn-danger">CANCEL</a>
							{% endif %}
						</td>
					</tr>
					<tr>
						<td bgcolor="#e0ebeb" colspan="2">
							<b><font color="#000099">Total Pembayaran</font></b>
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
						<td colspan="2">
							<b><font color="#000099">Tgl. Order</font></b>
							<br>
							{{ order.created_at }}
						</td>
						<td colspan="2">
							<b><font color="#000099">From IP</font></b>
							<br>
							{{ order.ip_address }}
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
