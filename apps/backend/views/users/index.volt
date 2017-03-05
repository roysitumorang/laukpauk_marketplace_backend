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
						<li>
							<a href="/admin">
								<i class="fa fa-home"></i>
							</a>
						</li>
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
				{{ flashSession.output() }}
				<i class="fa fa-plus-square"></i>&nbsp;<a href="/admin/users/create" title="Tambah Member">Tambah Member</a><br><br>
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
								<select name="status">
									{% for value, label in status %}
									<option value="{{ value }}"{% if current_status == value %} selected{% endif %}>{{ label }}</option>
									{% endfor %}
								</select>&nbsp;&nbsp;
								<select name="role_id">
									<option value="">Any Roles</option>
									{% for role in roles %}
									<option value="{{ role.id }}"{% if current_role == role.id %} selected{% endif %}>{{ role.name }}</option>
									{% endfor %}
								</select>&nbsp;&nbsp;
								<input type="text" name="keyword" value="{{ keyword }}" size="20" placeholder="Name / Email / Phone">&nbsp;
								<button type="submit" class="btn btn-info">CARI</button>
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
								<font size="4"><a href="/admin/users/{{ user.id }}" title="{{ user.name }}">{{ user.name }}</a></font>
								<br>
								<i class="fa fa-users"></i>&nbsp;&nbsp;{{ user.role }}<br>
								{% if user.email %}
								<i class="fa fa-envelope"></i>&nbsp;&nbsp;<a href="mailto:{{ user.email }}" target="_blank">{{ user.email }}</a><br>
								{% endif %}
								<i class="fa fa-key"></i>&nbsp;&nbsp;{{ user.api_key | default('-') }}<br>
								<i class="fa fa-phone-square"></i>&nbsp;&nbsp;{{ user.mobile_phone }}<br>
								<i class="fa fa-sign-in"></i>&nbsp;
								{% if user.last_login %}
								<a href="/admin/users/{{ user.id }}/login_history">{{ user.last_login }}</a>
								{% else %}
								No login yet
								{% endif %}
								{% if user.village %}
								<br><i class="fa fa-home"></i>&nbsp;&nbsp;{{ user.village }}, {{ user.subdistrict }}
								{% endif %}
							</td>
							<td>
								Join Date {{ date('d M Y', strtotime(user.created_at)) }}<br>
								<br><i class="fa fa-money"></i>&nbsp;Rp. {{ number_format(user.deposit) }}
								{% if in_array('Merchant', user.roles) or in_array('Buyer', user_roles) %}
								<br><a href="/admin/orders?{% if in_array('Merchant', user.roles) %}merchant_id{% else %}buyer_id{% endif %}={{ user.id }}">Orders: {{ user.total_orders }}</a>
								<br><a href="/admin/orders?{% if in_array('Merchant', user.roles) %}merchant_id{% else %}buyer_id{% endif %}={{ user.id }}&amp;status=0">Pending Orders: {{ user.total_pending_orders }} / Rp. {{ number_format(user.total_pending_bill) }}</a>
								<br><a href="/admin/orders?{% if in_array('Merchant', user.roles) %}merchant_id{% else %}buyer_id{% endif %}={{ user.id }}&amp;status=1">Completed Orders: {{ user.total_completed_orders }} / Rp. {{ number_format(user.total_completed_bill) }}</a>
								<br><a href="/admin/orders?{% if in_array('Merchant', user.roles) %}merchant_id{% else %}buyer_id{% endif %}={{ user.id }}&amp;status=-1">Cancelled Orders: {{ user.total_cancelled_orders }}</a>
								{% endif %}
								{% if in_array('Merchant', user.roles) %}
								<br><a href="/admin/users/{{ user.id }}/store_items">Products: {{ user.total_products }}</a>
								<br><a href="/admin/users/{{ user.id }}/service_areas">Service Areas: {{ user.total_service_areas }}</a>
								{% endif %}
							</td>
							<td>
								{% if user.status == 'HOLD' %}
								<a href="javascript:confirm('Anda yakin mengaktifkan member ini ?')&amp;&amp;(location.href='/admin/users/{{ user.id }}/activate')" title="Activated"><img src="/backend/images/bullet-red.png" border="0"></a>
								<b><font color="#FF0000">HOLD</font></b>&nbsp;
								<a href="javascript:open_window('/admin/emails/create?user_id={{ user.id }}')" title="send email"><img src="/backend/images/send-email-small.png" border="0"></a>
								{% elseif user.status == 'ACTIVE' %}
								<a href="javascript:confirm('Anda yakin menonaktifkan member ini ?')&amp;&amp;(location.href='/admin/users/{{ user.id }}/suspend')" title="Hold"><img src="/backend/images/bullet-green.png" border="0"></a>&nbsp;<b>ACTIVE</b>
								{% else %}
								<a href="javascript:confirm('Anda yakin mengaktifkan kembali member ini ?')&amp;&amp;(location.href='/admin/users/{{ user.id }}/reactivate')" title="Reactivated"><img src="/backend/images/bullet-red.png" border="0"></a>
								<b><font color="#FF0000">SUSPENDED</font></b>
								{% endif %}
								<br><br>
								{% if user.verified_at %}
								<img src="/backend/images/bullet-green.png" border="0">&nbsp;
								<b><font color="#000000">VERIFIED</font></b>
								{% elseif user.status == 'ACTIVE' %}
								<a href="javascript:confirm('Anda yakin ingin melakukan verifikasi terhadap member ini ?')&amp;&amp;(location.href='/admin/users/{{ user.id }}/verify')" title="Verify"><img src="/backend/images/bullet-red.png" border="0"></a>&nbsp;
								<b><font color="#FF0000">VERIFICATION IN PROGRESS</font></b>
								{% endif %}
							</td>
							<td>
								{% if user.status == 'ACTIVE' %}
								<a href="/admin/users/{{ user.id }}/update" title="Ubah"><i class="fa fa-pencil-square fa-2x"></i></a><br>
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
				{% if page.total_pages > 1 %}
				<div class="weepaging">
					<p>
						<b>Halaman:</b>&nbsp;&nbsp;
						{% for i in pages %}
							{% if i == page.current %}
							<b>{{ i }}</b>
							{% else %}
							<a href="/admin/users/index/page:{{ i }}{% if query_string %}?{{ query_string }}{% endif %}">{{ i }}</a>
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
