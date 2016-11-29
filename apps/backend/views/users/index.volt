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
				<a href="/admin/users"><h2>Member List</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li>
							<a href="/admin">
								<i class="fa fa-home"></i>
							</a>
						</li>
						<li><span>Member List</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Member List</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				{{ flashSession.output() }}
				<i class="fa fa-plus-square"></i>&nbsp;<a href="/admin/users/create" title="Tambah Member">Members Add</a><br><br>
				<div style="padding:10px;background:#e5f2ff;font-size:14px;color:#333333">
					<strong>Total Members:</strong>&nbsp;{{ total_users }} members /
					<font size="2">
						<strong>Pending:</strong>&nbsp;{{ total_pending_users }} /
						<strong>Aktif:</strong>&nbsp;{{ total_active_users }} /
						<strong>Suspended:</strong>&nbsp;{{ total_suspended_users }}
					</font>
				</div>
				<table class="table table-striped">
					<tr>
						<td>
							<!-- Main Content //-->
							<form action="/admin/users" method="GET">
								<b>Cari berdasarkan:</b>
								<select name="status" class="form form-control form-20">
									{% for value, label in status %}
									<option value="{{ value }}"{% if current_status == value %} selected{% endif %}>{{ label }}</option>
									{% endfor %}
								</select>&nbsp;&nbsp;
								<select name="role_id" class="form form-control form-20">
									<option value="">Any Roles</option>
									{% for role in roles %}
									<option value="{{ role.id }}"{% if current_role == role.id %} selected{% endif %}>{{ role.name }}</option>
									{% endfor %}
								</select>&nbsp;&nbsp;
								<input type="text" name="keyword" value="{{ keyword }}" class="form form-control form-20" size="20" placeholder="Name / Email / Phone">&nbsp;
								<button type="submit" class="btn btn-info">CARI</button>
								<input type="submit" name="print" value="Excel" class="btn btn-success">&nbsp;
								<input type="submit" name="print" value="CSV" class="btn btn-warning">
							</form>
						</td>
					</tr>
				</table>
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
								<font size="4"><a href="/admin/users/show/{{ user.id }}" title="{{ user.name }}">{{ user.name }}</a></font>
								<br>
								<i class="fa fa-users"></i>&nbsp;&nbsp;{{ user.role }}<br>
								{% if user.email %}
								<i class="fa fa-envelope"></i>&nbsp;&nbsp;<a href="mailto:{{ user.email }}" target="_blank">{{ user.email }}</a><br>
								{% endif %}
								<i class="fa fa-phone-square"></i>&nbsp;&nbsp;<a href="/admin/sms/create/user_id:{{ user.id }}" target="_blank">{{ user.phone }}</a><br>
								<i class="fa fa-envelope"></i>&nbsp;&nbsp;<a href="/admin/users/emails/{{ user.id }}" title="email log">{{ count(user.emails) }} emails</a><br>
								<i class="fa fa-sign-in"></i>&nbsp;
								{% if !user.last_seen %}
								No login yet
								{% else %}
								<a href="/admin/users/login_history/{{ user.id }}">{{ user.last_seen }}</a>
								{% endif %}
								{% if user.village %}
								<br><i class="fa fa-home"></i>&nbsp;&nbsp;{{ user.village }}, {{ user.subdistrict }}
								{% endif %}
							</td>
							<td>
								Reg Date {{ date('d M Y', strtotime(user.created_at)) }}<br>
								<i class="fa fa-user"></i>&nbsp;
								{% if !user.premium %}
								<b><font color="#000099">FREE</font></b>
								{% else %}
								<b><font color="#009900">PREMIUM</font></b>
								{% endif %}
								<br><i class="fa fa-money"></i>&nbsp;Rp. {{ number_format(user.deposit) }}
								<br>Points: {{ number_format(user.buy_point) }}
								{% if user.role == 'Merchant' or user_role == 'Buyer' %}
								<br><a href="/admin/orders?{% if user.role == 'Merchant' %}merchant_id{% else %}buyer_id{% endif %}={{ user.id }}">Orders: {{ user.total_orders }}</a>
								<br><a href="/admin/orders?{% if user.role == 'Merchant' %}merchant_id{% else %}buyer_id{% endif %}={{ user.id }}&amp;status=0">Pending Orders: {{ user.total_pending_orders }} / Rp. {{ number_format(user.total_pending_bill) }}</a>
								<br><a href="/admin/orders?{% if user.role == 'Merchant' %}merchant_id{% else %}buyer_id{% endif %}={{ user.id }}&amp;status=1">Completed Orders: {{ user.total_completed_orders }} / Rp. {{ number_format(user.total_completed_bill) }}</a>
								<br><a href="/admin/orders?{% if user.role == 'Merchant' %}merchant_id{% else %}buyer_id{% endif %}={{ user.id }}&amp;status=-1">Cancelled Orders: {{ user.total_cancelled_orders }}</a>
								{% endif %}
								{% if user.role == 'Merchant' %}
								<br><a href="/admin/product_prices?user_id={{ user.id }}">Products: {{ user.total_products }}</a>
								<br><a href="/admin/service_areas?user_id={{ user.id }}">Service Areas: {{ user.total_service_areas }}</a>
								{% endif %}
							</td>
							<td>
								{% if user.status == 'HOLD' %}
								<a href="javascript:confirm('Anda yakin mengaktifkan member ini ?')&amp;&amp;(location.href='/admin/users/activate/{{ user.id }}')" title="Activated"><img src="/backend/images/bullet-red.png" border="0"></a>
								<b><font color="#FF0000">HOLD</font></b>&nbsp;
								<a href="javascript:open_window('/admin/emails/create?user_id={{ user.id }}')" title="send email"><img src="/backend/images/send-email-small.png" border="0"></a>
								{% elseif user.status == 'ACTIVE' %}
								<a href="javascript:confirm('Anda yakin menonaktifkan member ini ?')&amp;&amp;(location.href='/admin/users/suspend/{{ user.id }}')" title="Hold"><img src="/backend/images/bullet-green.png" border="0"></a>&nbsp;<b>ACTIVE</b>
								{% else %}
								<a href="javascript:confirm('Anda yakin mengaktifkan kembali member ini ?')&amp;&amp;(location.href='/admin/users/reactivate/{{ user.id }}')" title="Reactivated"><img src="/backend/images/bullet-red.png" border="0"></a>
								<b><font color="#FF0000">SUSPENDED</font></b>
								{% endif %}
								<br><br>
								{% if user.verified_at %}
								<img src="/backend/images/bullet-green.png" border="0">&nbsp;
								<b><font color="#000000">VERIFIED</font></b>
								{% elseif user.status == 'ACTIVE' %}
								<a href="javascript:confirm('Anda yakin ingin melakukan verifikasi terhadap member ini ?')&amp;&amp;(location.href='/admin/users/verify/{{ user.id }}')" title="Verify"><img src="/backend/images/bullet-red.png" border="0"></a>&nbsp;
								<b><font color="#FF0000">VERIFICATION IN PROGRESS</font></b>
								{% endif %}
							</td>
							<td>
								{% if user.status == 'ACTIVE' %}
								<a href="/admin/users/update/{{ user.id }}" title="Ubah"><i class="fa fa-pencil-square fa-2x"></i></a><br>
								{% endif %}
								{#
								<a href="javascript:void(0)" class="delete" data-id="{{ user.id }}" title="Hapus"><i class="fa fa-trash-o fa-2x"></i></a>
								#}
							</td>
						</tr>
						{% elsefor %}
						<tr>
							<td colspan="5">No Members List</td>
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
							<a href="/admin/users/index/page:{{ i }}{% if current_status or current_role or keyword %}?{% endif %}{% if current_status %}status={{ current_status }}{% endif %}{% if current_role %}&amp;role_id={{ current_role }}{% endif %}{% if keyword %}&amp;keyword={{ keyword }}{% endif %}">{{ i }}</a>
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
