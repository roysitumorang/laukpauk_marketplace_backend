<section class="body">
	<!-- start: header -->
	{include file='top_menu.html'}
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
				<i class="fa fa-plus-square"></i>&nbsp;<a href="/admin/users/new" title="Tambah Member">Members Add</a><br><br>
				<div style="padding:10px;background:#e5f2ff;font-size:14px;color:#333333">
					<strong>Total Members:</strong>&nbsp;{{ count(unbanned_users) }} members /
					<font size="2">
						<strong>Aktif:</strong>&nbsp;{{ count(active_users) }} /
						<strong>Pending:</strong>&nbsp;{{ count(pending_users) }}
					</font>
				</div>
				<table class="table table-striped">
					<tr>
						<td>
							<!-- Main Content //-->
							<form action="/admin/members" method="GET">
								<b>Cari berdasarkan:</b>
								<select name="vCompare" class="form form-control form-20">
									<option{if $vCompare == 'vUsername'} selected{/if} value="vUsername">Username</option>
									<option{if $vCompare == 'vName'} selected{/if} value="vName">Nama Member</option>
									<option{if $vCompare == 'vEmail'} selected{/if} value="vEmail">Email</option>
									<option{if $vCompare == 'vCity'} selected{/if} value="vCity">Kota</option>
									<option{if $vCompare == 'vZIP'} selected{/if} value="vZIP">Kode Pos</option>
									<option{if $vCompare == 'iType'} selected{/if} value="iType">Member Type</option>
									<option{if $vCompare == 'dReg'} selected{/if} value="dReg">Registration Date (YYYY-mm-dd YYYY-mm-dd)</option>
									<option{if $vCompare == 'dAktif'} selected{/if} value="dAktif">Active Date (YYYY-mm-dd YYYY-mm-dd)</option>
									<option{if $vCompare == 'iStatus'} selected{/if} value="iStatus">Member Active Status</option>
								</select>&nbsp;&nbsp;
								<input type="text" name="vTeks" value="{$vTeks}" class="form form-control form-40" size="40">&nbsp;
								<input type="submit" name="submit" value="CARI" class="btn btn-info">&nbsp;
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
							<th><b>Username</b></th>
							<th><b>Name</b></th>
							<th colspan="2"><b>Status</b></th>
							<th><b>#</b></th>
						</tr>
					</thead>
					<tbody>
					{% if !users %}
						<tr>
							<td colspan="9">No Members List</td>
						</tr>
					{% else %}
						{% for user in users %}
						<tr>
							<td>{{ user.no }}</td>
							<td><a href="/admin/users/{{ user.id }}" title="{{ user.name }}">{{ user.username }}</a></td>
							<td>
								<font size="4"><a href="/admin/users/{{ user.id }}" title="{{ user.name }}">{{ user.name }}</a></font>
								<br>
								<i class="fa fa-envelope"></i>&nbsp; <a href="mailto:{{ user.email }}" target="_blank">{{ user.email }}</a><br>
								<i class="fa fa-phone-square"></i>&nbsp;&nbsp;<a href="/admin/sms/new?user_id={{ user.id }}" target="_blank">{{ user.mobile }}</a><br>
								<a href="/admin/users/{{ user.id }}/emails" title="email log"><i class="fa fa-envelope"></i>&nbsp;{{ count(user.emails) }} emails</a>
								<br><i class="fa fa-sign-in"></i>&nbsp;
								{% if !user.last_login %}
								No login yet
								{% else %}
								<a href="/admin/users/{{ user.id }}/login_history">{{ user.last_login }}</a>
								{% endif %}
							</td>
							<td>
								Reg Date:&nbsp;{{ date_format(user.created_at, '%d %B %Y') }}<br>
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
								{% if user.status == 'hold' %}
								<a href="javascript:confirm('Anda yakin aktifkan member ini ?')&&(location.href='/admin/users/{{ user.id }}/activate')" title="Activated"><img src="/backend/images/bullet-red.png" border="0"></a>
								<b><font color="#FF0000">HOLD</font></b> ({$listMembers[i].Item.vAktif|no_value})&nbsp;
								<a href="javascript:open_window('/admin/emails/new?user_id={{ user.id }}')" title="send email"><img src="/backend/images/send-email-small.png" border="0"></a>
								{% else %}
								<a href="javascript:confirm('Anda yakin menonaktifkan member ini ?')&&(location.href='/admin/users/{{ user.id }}/deactivate')" title="Hold"><img src="assets/images/bullet-green.png" border="0"></a>&nbsp;<b>ACTIVE</b>
								{% endif %}
								<br><br>
								{% if user.status == 'active' and !user.verified_at %}
								<a href="javascript:confirm('Anda yakin ingin melakukan verifikasi terhadap member ini ?')&&(location.href='/admin/users/{{ user.id }}/verify')" title="Verify Progress"><img src="/backend/images/bullet-green.png" border="0"></a>&nbsp;
								<b><font color="#000000">VERIFIED</font></b>
								{% else %}
								<a href="javascript:confirm('Anda yakin sudah memverifikasi member ini ?')&&(location.href='/admin/users/{{ user.id }}/unverify')" title="Verify Member Ini"><img src="/backend/images/bullet-red.png" border="0"></a>&nbsp;<font color="#FF0000"><b>VERIFY IN PROGRESS</b></font>
								{% endif %}
							</td>
							<td>
								<a href="/admin/users/{{ user.id }}/edit" title="Ubah"><i class="fa fa-pencil-square fa-2x"></i></a><br>
								<a href="javascript:confirm('Are you sure to delete this member ?')&&(location.href='/admin/users/{{ user.id }}/delete')" title="Hapus"><i class="fa fa-trash-o fa-2x"></i></a>
							</td>
						</tr>
						{% endfor %}
					{% endif %}
					</tbody>
				</table>
				{% if users %}
				<div class="weepaging">
					<p>
						<b>Halaman:</b>&nbsp;&nbsp;
						{% if !memberLink %}
						<i>None</i>
						{% else %}
						{{ memberLink }}
						{% endif %}
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