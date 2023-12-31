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
				<a href="/admin/users"><h2>Daftar Member</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span>Daftar Member</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Daftar Member</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				{{ partial('partials/tabs_user', ['expand': 'users']) }}
				<div class="tab-content">
					<div id="users" class="tab-pane active">
						{{ flashSession.output() }}
						<div style="padding:10px;background:#e5f2ff;font-size:14px;color:#333333">
							<strong>Total Members:</strong>&nbsp;{{ total_users }} members /
							<font size="2">
								<strong>Pending:</strong>&nbsp;{{ total_pending_users }} /
								<strong>Aktif:</strong>&nbsp;{{ total_active_users }} /
								<strong>Suspended:</strong>&nbsp;{{ total_suspended_users }}
							</font>
						</div>
						{{ form('/admin/users', 'method': 'GET', 'id': 'search') }}
							<table class="table table-striped">
								<tr>
									<td><i class="fa fa-tag"></i> Status</td>
									<td>
										{{ select_static('status', user_status, 'value': current_status) }}
									</td>
									<td class="text-nowrap"><i class="fa fa-users"></i> Role</td>
									<td>
										{{ select_static('role_id', roles, 'using': ['id', 'name'], 'value': current_role, 'useEmpty': true, 'emptyText': '- semua -', 'emptyValue': '') }}
									</td>
								</tr>
								<tr>
									<td class="text-nowrap" colspan="4">
										<i class="fa fa-user"></i> Nama / Toko / Nomor HP
										{{ text_field('keyword', 'value': keyword, 'size': 20, 'placeholder': 'Nama / Toko / Nomor HP') }}
										<button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Cari</button>
										<a type="button" href="/admin/users/excel" target="_blank" class="btn btn-success"><i class="fa fa-file-excel-o"></i> Excel</a>
									</td>
								</tr>
							</table>
						{{ endForm() }}
						<table class="table table-striped">
							<thead>
								<tr>
									<th width="5%"><b>No</b></th>
									<th><b>Name</b></th>
									<th colspan="2"><b>Status</b></th>
									<th><b>#</b></th>
								</tr>
							</thead>
							<tbody>
								{% for user in users %}
								<tr id="{{ user.id }}">
									<td>{{ user.rank }}</td>
									<td>
										<font size="4"><a href="/admin/users/{{ user.id }}" title="{{ user.name }}">{{ user.name }}{% if user.company %} / {{ user.company }}{% endif %}</a></font>
										<br>
										<i class="fa fa-users"></i>&nbsp;&nbsp;{{ user.role }}<br>
										{% if user.email %}
										<i class="fa fa-envelope"></i>&nbsp;&nbsp;<a href="mailto:{{ user.email }}" target="_blank">{{ user.email }}</a><br>
										{% endif %}
										<i class="fa fa-key"></i>&nbsp;&nbsp;{{ user.api_key | orElse('-') }}<br>
										<i class="fa fa-mobile"></i>&nbsp;&nbsp;{{ user.mobile_phone }}<br>
										<i class="fa fa-sign-in"></i>&nbsp;
										{% if user.last_login %}
										<a href="/admin/users/{{ user.id }}/login_history">{{ user.last_login }}</a>
										{% else %}
										No login yet
										{% endif %}
										{% if user.village %}
										<br><i class="fa fa-home"></i>&nbsp;&nbsp;{{ user.village }}, {{ user.subdistrict }}, {{ user.city }}, {{ user.province }}
										{% endif %}
										{% if user.latitude and user.longitude %}
										<br><i class="fa fa-map-marker"></i>&nbsp;&nbsp;{{ user.latitude }}, {{ user.longitude }}
										{% endif %}
									</td>
									<td>
										<i class="fa fa-calendar-check-o"></i> {{ user.created_at | datetime }}<br>
										<br><i class="fa fa-money"></i>&nbsp;{% if user.deposit %}Rp. {{ user.deposit | number_format }}{% else %}-{% endif %}
									</td>
									<td>
										{% if user.status == 'HOLD' %}
										<a href="javascript:void(0)" onclick="confirm('Anda yakin mengaktifkan member ini ?')&amp;&amp;(location.href='/admin/users/{{ user.id }}/activate')" title="Hold"><font color="#FF0000"><i class="fa fa-user-o fa-2x"></i></font></a>
										{% elseif user.status == 'ACTIVE' %}
										<a href="javascript:void(0)" onclick="confirm('Anda yakin menonaktifkan member ini ?')&amp;&amp;(location.href='/admin/users/{{ user.id }}/suspend')" title="Active"><i class="fa fa-user fa-2x"></i></a>
										{% else %}
										<a href="javascript:void(0)" onclick="confirm('Anda yakin mengaktifkan kembali member ini ?')&amp;&amp;(location.href='/admin/users/{{ user.id }}/reactivate')" title="Suspended"><font color="#FF0000"><i class="fa fa-user-times fa-2x"></i></font></a>
										{% endif %}
									</td>
									<td>
										{% if user.status == 'ACTIVE' %}
											<a href="/admin/users/{{ user.id }}" title="Detail"><i class="fa fa-external-link fa-2x"></i></a>
											{% if user.role == 'Buyer' %}
												<a href="/admin/orders/create/buyer_id:{{ user.id }}"><i class="fa fa-cart-plus fa-2x"></i></a>
											{% endif %}
										{% endif %}
									</td>
								</tr>
								{% elsefor %}
								<tr>
									<td colspan="5">Belum ada member</td>
								</tr>
								{% endfor %}
							</tbody>
						</table>
						{% if pagination.total_pages > 1 %}
						<div class="weepaging">
							<p>
								<b>Halaman:</b>&nbsp;&nbsp;
								{% for i in pages %}
									{% if i == pagination.current %}
										<b>{{ i }}</b>
									{% else %}
										<a href="/admin/users/index{% if current_status %}/status:{{ current_status }}{% endif %}{% if current_role %}/role_id:{{ current_role }}{% endif %}{% if keyword %}/keyword:{{ keyword }}{% endif %}{% if i > 1 %}/page:{{ i }}{% endif %}">{{ i }}</a>
									{% endif %}
								{% endfor %}
							</p>
						</div>
					</div>
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
		let url = '/admin/users/index', replacement = {' ': '+', ':': '', '\/': ''};
		event.preventDefault(),
		event.target.status.value && (url += '/status:' + event.target.status.value),
		event.target.role_id.value && (url += '/role_id:' + event.target.role_id.value),
		event.target.keyword.value && (url += '/keyword:' + event.target.keyword.value.trim().replace(/ |:|\//g, match => {
			return replacement[match];
		})),
		location.href = url
	}, false)
</script>
