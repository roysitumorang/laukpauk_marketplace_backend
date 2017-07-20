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
				<a href="/users"><h2>Daftar Member</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li>
							<a href="/">
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
						<table class="table table-striped">
							<tr>
								<td>
									<!-- Main Content //-->
									<form action="/users" method="GET" id="search">
										<b>Cari berdasarkan:</b>
										<select name="status">
											{% for value, label in status %}
											<option value="{{ value }}"{% if current_status == value %} selected{% endif %}>{{ label }}</option>
											{% endfor %}
										</select>&nbsp;&nbsp;
										<input type="text" name="keyword" value="{{ keyword }}" size="20" placeholder="Nama / Nomor HP">&nbsp;
										<button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Cari</button>
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
										<font size="4"><a href="/users/{{ user.id }}" title="{{ user.name }}">{{ user.name }}</a></font>
										<br>
										<i class="fa fa-phone-square"></i>&nbsp;&nbsp;{{ user.mobile_phone }}<br>
										<i class="fa fa-sign-in"></i>&nbsp;
										{% if user.last_login %}
										<a href="/users/{{ user.id }}/login_history">{{ user.last_login }}</a>
										{% else %}
										No login yet
										{% endif %}
										{% if user.village %}
										<br><i class="fa fa-home"></i>&nbsp;&nbsp;{{ user.village }}, {{ user.subdistrict }}, {{ user.city }}, {{ user.province }}
										{% endif %}
									</td>
									<td>
										Join Date {{ date('d M Y', strtotime(user.created_at)) }}<br>
									</td>
									<td>
										{% if user.status == 'HOLD' %}
										<a href="javascript:confirm('Anda yakin mengaktifkan member ini ?')&amp;&amp;(location.href='/users/{{ user.id }}/activate')" title="Activated"><img src="/backend/images/bullet-red.png" border="0"></a>
										<b><font color="#FF0000">HOLD</font></b>&nbsp;
										<a href="javascript:open_window('/emails/create?user_id={{ user.id }}')" title="send email"><img src="/backend/images/send-email-small.png" border="0"></a>
										{% elseif user.status == 'ACTIVE' %}
										<a href="javascript:confirm('Anda yakin menonaktifkan member ini ?')&amp;&amp;(location.href='/users/{{ user.id }}/suspend')" title="Hold"><img src="/backend/images/bullet-green.png" border="0"></a>&nbsp;<b>ACTIVE</b>
										{% else %}
										<a href="javascript:confirm('Anda yakin mengaktifkan kembali member ini ?')&amp;&amp;(location.href='/users/{{ user.id }}/reactivate')" title="Reactivated"><img src="/backend/images/bullet-red.png" border="0"></a>
										<b><font color="#FF0000">SUSPENDED</font></b>
										{% endif %}
									</td>
									<td>
										{% if user.status == 'ACTIVE' %}
										<a href="/users/{{ user.id }}/update" title="Ubah"><i class="fa fa-pencil-square fa-2x"></i></a><br>
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
									<a href="/users/index{% if current_status %}/status:{{ current_status }}{% endif %}{% if keyword %}/keyword:{{ keyword }}{% endif %}{% if i > 1 %}/page:{{ i }}{% endif %}">{{ i }}</a>
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
	let search = document.getElementById('search'), url = '/users/index', replacement = {' ': '+', ':': '', '\/': ''};
	search.addEventListener('submit', event => {
		event.preventDefault();
		if (search.status.value) {
			url += '/status:' + search.status.value;
		}
		if (search.keyword.value) {
			url += '/keyword:' + search.keyword.value.trim().replace(/ |:|\//g, match => {
				return replacement[match];
			});
		}
		location.href = url;
	}, false)
</script>
