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
				<a href="/admin/users/show/{{ user.id }}"><h2>Detail Member #{{ user.id }}</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span><a href="/admin/users">Daftar Member</a></span></li>
						<li><span><a href="/admin/users/show/{{ user.id}}">Detail Member #{{ user.id }}</a></span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Detail Member #{{ user.id }}</h2>
				<span style="float:right;margin-top:-20px"><a href="/admin/users/update/{{ user.id }}" title="Ubah"><i class="fa fa-pencil-square fa-2x"></i></a></span>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				{{ flashSession.output() }}
				<table class="table table-striped">
					<thead>
						<tr>
							<th><b>Nama</b></td>
							<th><b>Membership</b></td>
							<th><b>Status</b></td>
							<th><b>Dompet</b></td>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>
								<font size="5">{{ user.name }}</font>
								<br>
								{% if user.email %}
								<i class="fa fa-envelope"></i>&nbsp;<a href="mailto:{{ user.email }}" target="_blank">{{ user.email }}</a><br>
								{% endif %}
								<i class="fa fa-phone-square"></i>&nbsp;&nbsp;{{ user.mobile_phone }}
							</td>
							<td>
								<i class="fa fa-sign-in"></i>&nbsp;{{ last_login | default('No login yet') }}
							</td>
							<td>
								<b>{{ status[user.status] }}</b>
								<br><br>
								{% if user.verified_at %}
								<font color="#000000"><b>VERIFIED</font></b></font>
								{% else %}
								<font color="#FF0000"><b>VERIFICATION IN PROGRESS</b></font>
								{% endif %}
							</td>
							<td>Rp. {{ number_format(user.deposit) }}</td>
						</tr>
					</tbody>
				</table>
				<table class="table table-striped">
					<tr>
						<td rowspan="7" width="5%">
						{% if user.avatar %}
							<img src="/assets/images/{{ user.thumbnail }}" border="0"><br>
							<form method="POST" action="/admin/users/update/{{ user.id }}/delete_avatar:1" onsubmit="if(!confirm('Anda yakin menghapus gambar ini ?'))return !1">
								<button type="submit"><i class="fa fa-trash-o fa-2x"></i></button>
							</form>
						{% else %}
							<img src="/assets/images/no_picture_300.png" border="0">
						{% endif %}
						</td>
					</tr>
					<tr>
						<td>Tanggal Lahir</td>
						<td>{{ user.date_of_birth | default('Belum ada data') }}</td>
					</tr>
					<tr>
						<td>Jenis Kelamin</td>
						<td>{{ user.gender | default('Belum ada data') }}</td>
					</tr>
					<tr>
						<td>Nama Usaha</td>
						<td>{{ user.company | default('-') }}</td>
					</tr>
				</table>
				<table class="table table-striped">
					<tr>
						<td>Alamat</td>
						<td>{{ user.address }}</td>
					</tr>
					<tr>
						<td>Kelurahan</td>
						<td>{{ user.village.name }}</td>
					</tr>
					<tr>
						<td>Kecamatan</td>
						<td>{{ user.village.subdistrict.name }}</td>
					</tr>
					<tr>
						<td>Tanggal Pendaftaran</td>
						<td>{{ user.created_at }}</td>
					</tr>
					<tr>
						<td>Tanggal Aktivasi</td>
						<td>{{ user.activated_at | default('Belum aktif') }}</td>
					</tr>
					<tr>
						<td>Daftar dari IP</td>
						<td>{{ user.registration_ip }}</td>
					</tr>
					<tr>
						<td>Order</td>
						<td>{{ total.orders }}</td>
					</tr>
					<tr>
						<td>Pending Order</td>
						<td>{{ total.pending_orders }}</td>
					</tr>
					<tr>
						<td>Completed Order</td>
						<td>{{ total.completed_orders }}</td>
					</tr>
					<tr>
						<td>Cancelled Order</td>
						<td>{{ total.cancelled_orders }}</td>
					</tr>
					{% if roles['Merchant'] %}
					<tr>
						<td>Produk</td>
						<td>{{ products }}</td>
					</tr>
					<tr>
						<td>Service Area</td>
						<td>{{ service_areas }}</td>
					</tr>
					{% endif %}
				</table>
				<!-- eof Content //-->
			</div>
			<!-- end: page -->
		</section>
	</div>
	{{ partial('partials/right_side') }}
</section>