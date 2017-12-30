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
										<th><i class="fa fa-user"></i> Nama</td>
										<th><i class="fa fa-sign-in"></i> Login Terakhir</td>
										<th><i class="fa fa-tag"></i> Status</td>
										<th><i class="fa fa-money"></i> Deposit</td>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td>
											<font size="5">{{ user.name }}{% if user.company %} ({{ user.company }}){% endif %}</font>
										</td>
										<td>{{ last_login | orElse('No login yet') }}</td>
										<td>{{ status[user.status] }}</td>
										<td>{% if user.deposit %}Rp. {{ user.deposit | number_format }}{% else %}-{% endif %}</td>
									</tr>
								</tbody>
							</table>
							<table class="table table-striped">
								<tr>
									<td rowspan="{% if user.email %}9{% else %}8{% endif %}" width="5%">
									{% if user.avatar %}
										<img src="/assets/image/{{ user.thumbnail }}" border="0"><br>
										<form method="POST" action="/admin/users/{{ user.id }}/delete_avatar" onsubmit="if(!confirm('Anda yakin menghapus gambar ini ?'))return !1">
											<button type="submit"><i class="fa fa-trash-o fa-2x"></i></button>
										</form>
									{% else %}
										<img src="/assets/image/no_picture_300.png" border="0">
									{% endif %}
									</td>
								</tr>
								<tr>
									<td><i class="fa fa-users"></i> Role</td>
									<td>{{ user.role.name }}</td>
								</tr>
								<tr>
									<td><i class="fa fa-key"></i> API Key</td>
									<td>{{ user.api_key }}</td>
								</tr>
								{% if user.email %}
								<tr>
									<td><i class="fa fa-email"></i> Email</td>
									<td>{{ user.email }}</td>
								</tr>
								{% endif %}
								<tr>
									<td><i class="fa fa-calendar-o"></i> Tanggal Lahir</td>
									<td>{{ user.date_of_birth | orElse('Belum ada data') }}</td>
								</tr>
								<tr>
									<td><i class="fa fa-venus-mars"></i> Jenis Kelamin</td>
									<td>{{ user.gender | orElse('Belum ada data') }}</td>
								</tr>
								{% if user.role.name == 'Merchant' %}
								<tr>
									<td><i class="fa fa-calendar"></i> Buka</td>
									<td>{{ user.businessDays() }} ({{ user.business_opening_hour }}.00-{{ user.business_closing_hour }}.00 WIB)</td>
								</tr>
								<tr>
									<td><i class="fa fa-clock-o"></i> Pengantaran</td>
									<td>{{ user.deliveryHours() }}</td>
								</tr>
								{% endif %}
							</table>
							<table class="table table-striped">
								<tr>
									<td><i class="fa fa-map-marker"></i> Alamat</td>
									<td>{{ user.address | orElse('-') }}, {{ user.village.name }}, {{ user.village.subdistrict.name }}, {{ user.village.subdistrict.city.name }}, {{ user.village.subdistrict.city.province.name }}</td>
								</tr>
								<tr>
									<td><i class="fa fa-check-circle-o"></i> Tanggal / IP Pendaftaran / Aktivasi </td>
									<td>{{ user.created_at }} /{{ user.registration_ip }} / {{ user.activated_at | orElse('Belum aktif') }}
								</tr>
								<tr>
									<td><i class="fa fa-shopping-bag"></i> Total Order / Pending / Completed / Cancelled</td>
									<td>{{ total['orders'] }} / {{ total['pending_orders'] }} / {{ total['completed_orders'] }} / {{ total['cancelled_orders'] }}</td>
								</tr>
								{% if user.role.name == 'Merchant' %}
								<tr>
									<td><i class="fa fa-gift"></i> Total Produk</td>
									<td>{{ total_products }}</td>
								</tr>
								<tr>
									<td><i class="fa fa-map"></i> Total Area Operasional</td>
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