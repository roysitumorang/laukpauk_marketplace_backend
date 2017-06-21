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
				<a href="/admin/users/{{ user.id }}"><h2>{{ user.name }}</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span><a href="/admin/users">Daftar Member</a></span></li>
						<li><span>{{ user.name }}</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">{{ user.name }}</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				{{ flashSession.output() }}
				<div class="tabs">
					{{ partial('partials/tabs_user', ['user': user, 'expand': 'show_user']) }}
					<div class="tab-content">
						<div id="show_user" class="tab-pane active">
							<table class="table table-striped">
								<thead>
									<tr>
										<th><b>Nama</b></td>
										{% if user.merchant %}
										<th><b>Merchant</b></th>
										{% endif %}
										<th><b>Login Terakhir</b></td>
										<th><b>Status</b></td>
										<th><b>Deposit</b></td>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td>
											<font size="5">{{ user.name }}{% if user.company %} / {{ user.company }}{% endif %}{% if user.premium_merchant%} <i class="fa fa-check-circle"></i>{% endif %}</font>
										</td>
										{% if user.merchant %}
										<td>
											{{ user.merchant.company }}
										</td>
										{% endif %}
										<td>
											<i class="fa fa-sign-in"></i>&nbsp;{{ last_login | default('No login yet') }}
										</td>
										<td>
											{{ status[user.status] }}
										</td>
										<td>Rp. {{ number_format(user.deposit) }}</td>
									</tr>
								</tbody>
							</table>
							<table class="table table-striped">
								<tr>
									<td rowspan="{% if user.email %}9{% else %}8{% endif %}" width="5%">
									{% if user.avatar %}
										<img src="/assets/image/{{ user.thumbnail }}" border="0"><br>
										<form method="POST" action="/admin/users/{{ user.id }}/deleteAvatar" onsubmit="if(!confirm('Anda yakin menghapus gambar ini ?'))return !1">
											<button type="submit"><i class="fa fa-trash-o fa-2x"></i></button>
										</form>
									{% else %}
										<img src="/assets/image/no_picture_300.png" border="0">
									{% endif %}
									</td>
								</tr>
								<tr>
									<td>Role</td>
									<td>{{ user.role.name }}</td>
								</tr>
								<tr>
									<td>API Key</td>
									<td>{{ user.api_key }}</td>
								</tr>
								{% if user.premium_merchant %}
								<tr>
									<td>Merchant Token</td>
									<td>{{ user.merchant_token }}</td>
								</tr>
								{% endif %}
								{% if user.email %}
								<tr>
									<td>Email</td>
									<td>{{ user.email }}</td>
								</tr>
								{% endif %}
								<tr>
									<td>Tanggal Lahir</td>
									<td>{{ user.date_of_birth | default('Belum ada data') }}</td>
								</tr>
								<tr>
									<td>Jenis Kelamin</td>
									<td>{{ user.gender | default('Belum ada data') }}</td>
								</tr>
								{% if user.role.name == 'Merchant' %}
								<tr>
									<td>Buka</td>
									<td>{{ user.businessDays() }} ({{ user.business_opening_hour }}.00-{{ user.business_closing_hour }}.00 WIB)</td>
								</tr>
								<tr>
									<td>Pengantaran</td>
									<td>{{ user.deliveryHours() }}</td>
								</tr>
								{% endif %}
							</table>
							<table class="table table-striped">
								<tr>
									<td>Alamat</td>
									<td>{{ user.address | default('-') }}, {{ user.village.name }}, {{ user.village.subdistrict.name }}, {{ user.village.subdistrict.city.name }}, {{ user.village.subdistrict.city.province.name }}</td>
								</tr>
								<tr>
									<td>Tanggal / IP Pendaftaran / Aktivasi </td>
									<td>{{ user.created_at }} /{{ user.registration_ip }} / {{ user.activated_at | default('Belum aktif') }}
								</tr>
								<tr>
									<td>Total Order / Pending / Completed / Cancelled</td>
									<td>{{ total.orders }} / {{ total.pending_orders }} / {{ total.completed_orders }} / {{ total.cancelled_orders }}</td>
								</tr>
								{% if user.role.name == 'Merchant' %}
								<tr>
									<td>Produk</td>
									<td>{{ total_products }}</td>
								</tr>
								<tr>
									<td>Service Area</td>
									<td>{{ total_coverage_areas }}</td>
								</tr>
								{% endif %}
							</table>
						</div>
					</div>
				</div>
				<!-- eof Content //-->
			</div>
			<!-- end: page -->
		</section>
	</div>
	{{ partial('partials/right_side') }}
</section>