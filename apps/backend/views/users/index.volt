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
						<strong>Aktif:</strong>&nbsp;{{ total_active_users }} /
						<strong>Pending:</strong>&nbsp;{{ total_pending_users }}
					</font>
				</div>
				<table class="table table-striped">
					<tr>
						<td>
							<!-- Main Content //-->
							<form action="/admin/users" method="GET">
								<b>Cari berdasarkan:</b>
								<select name="parameter" class="form form-control form-20">
									{% for value, label in search_parameters %}
									<option value="{{ value }}"{% if parameter == value %} selected{% endif %}>{{ label }}</option>
									{% endfor %}
								</select>&nbsp;&nbsp;
								<input type="text" name="keyword" value="{{ keyword }}" class="form form-control form-40" size="40">&nbsp;
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
					{% if !page.items %}
						<tr>
							<td colspan="5">No Members List</td>
						</tr>
					{% else %}
						{% for user in page.items %}
						<tr>
							<td>{{ user.no }}</td>
							<td>
								<font size="4"><a href="/admin/users/{{ user.id }}" title="{{ user.name }}">{{ user.name }}</a></font>
								<br>
								<i class="fa fa-envelope"></i>&nbsp; <a href="mailto:{{ user.email }}" target="_blank">{{ user.email }}</a><br>
								<i class="fa fa-phone-square"></i>&nbsp;&nbsp;<a href="/admin/sms/create/user_id:{{ user.id }}" target="_blank">{{ user.phone }}</a><br>
								<a href="/admin/users/{{ user.id }}/emails" title="email log"><i class="fa fa-envelope"></i>&nbsp;{{ count(user.emails) }} emails</a>
								<br><i class="fa fa-sign-in"></i>&nbsp;
								{% if !user.last_login %}
								No login yet
								{% else %}
								<a href="/admin/users/{{ user.id }}/login_history">{{ user.last_login }}</a>
								{% endif %}
							</td>
							<td>
								Reg Date:&nbsp;{{ date('d M Y', strtotime(user.created_at)) }}<br>
								<i class="fa fa-user"></i>&nbsp;
								{% if !user.premium %}
								<b><font color="#000099">FREE</font></b>
								{% else %}
								<b><font color="#009900">PREMIUM</font></b>
								{% endif %}
								<br><i class="fa fa-money"></i>&nbsp;Rp. {{ number_format(user.deposit) }}
								<br>
								Total Poin: {{ number_format(user.buy_point) }}
							</td>
							<td>
								{% if user.status == hold %}
								<a href="javascript:confirm('Anda yakin aktifkan member ini ?')&&(location.href='/admin/users/{{ user.id }}/activate')" title="Activated"><img src="/backend/images/bullet-red.png" border="0"></a>
								<b><font color="#FF0000">HOLD</font></b> ({{ user.status }})&nbsp;
								<a href="javascript:open_window('/admin/emails/new?user_id={{ user.id }}')" title="send email"><img src="/backend/images/send-email-small.png" border="0"></a>
								{% else %}
								<a href="javascript:confirm('Anda yakin menonaktifkan member ini ?')&&(location.href='/admin/users/{{ user.id }}/deactivate')" title="Hold"><img src="/backend/images/bullet-green.png" border="0"></a>&nbsp;<b>ACTIVE</b>
								{% endif %}
								<br><br>
								{% if user.status == active and !user.verified_at %}
								<a href="javascript:confirm('Anda yakin ingin melakukan verifikasi terhadap member ini ?')&&(location.href='/admin/users/{{ user.id }}/verify')" title="Verify Progress"><img src="/backend/images/bullet-green.png" border="0"></a>&nbsp;
								<b><font color="#000000">VERIFIED</font></b>
								{% else %}
								<a href="javascript:confirm('Anda yakin sudah memverifikasi member ini ?')&&(location.href='/admin/users/{{ user.id }}/unverify')" title="Verify Member Ini"><img src="/backend/images/bullet-red.png" border="0"></a>&nbsp;<font color="#FF0000"><b>VERIFY IN PROGRESS</b></font>
								{% endif %}
							</td>
							<td>
								<a href="/admin/users/update/{{ user.id }}" title="Ubah"><i class="fa fa-pencil-square fa-2x"></i></a><br>
								<a href="javascript:void(0)" class="delete" data-id="{{ user.id }}" title="Hapus"><i class="fa fa-trash-o fa-2x"></i></a>
							</td>
						</tr>
						{% endfor %}
					{% endif %}
					</tbody>
				</table>
				{% if multi_page %}
				<div class="weepaging">
					<p>
						<b>Halaman:</b>&nbsp;&nbsp;
						<a href="/admin/users">1</a>
						<a href="/admin/users?page={{ page.before }}">{{ page.before }}</a>
						<a href="/admin/users?page={{ page.next }}">{{ page.next }}</a>
						<a href="/admin/users?page={{ page.last }}">{{ page.last }}</a>
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
