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
				<a href="/admin/coupons"><h2>Kupon Member</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span>Kupon</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">
					Kode Kupon: <strong>{{ coupon.code }}</strong>&nbsp;
					<img src="/assets/image/bullet-{% if coupon.status == 1 %}green{% else %}red{% endif %}.png" border="0">
				</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				{{ flashSession.output() }}
				{{ partial('partials/tabs_coupon', ['coupon': coupon, 'expand': 'users']) }}
				<p style="margin-left:5px"><i class="fa fa-users"></i>&nbsp;<a href="/admin/coupon_users/index/coupon_id:{{ coupon.id }}">Daftar Member</a></p>
				<form method="GET" action="/admin/coupon_users/create/coupon_id:{{ coupon.id }}">
					<table class="table table-striped">
						<tr>
							<td>
								<input type="text" name="keyword" value="{{ keyword }}" size="30" class="form form-control" placeholder="Nama / Nomor HP">
							</td>
							<td>
								<button type="submit" class="btn btn-info">CARI</button>
							</td>
						</tr>
					</table>
				</form>
				<form method="POST" action="/admin/coupon_users/create/coupon_id:{{ coupon.id }}" id="new_user">
					<table class="table table-striped">
						<thead>
							<tr>
								<th width="25"><b>No</b></th>
								<th><b>Nama</b></th>
								<th><b>Nomor HP</b></th>
								<th><b>#</b></th>
							</tr>
						</thead>
						<tbody>
						{% for user in users %}
							<tr>
								<td>{{ user.rank }}</td>
								<td>{{ user.name }}</td>
								<td>{{ user.mobile_phone }}</td>
								<td><input type="checkbox" name="user_id" value="{{ user.id }}"></i></td>
							</tr>
						{% endfor %}
						</tbody>
					</table>
				</form>
				{% if page.total_pages > 1 %}
				<div class="weepaging">
					<p>
						<b>Halaman:</b>&nbsp;&nbsp;
						{% for i in pages %}
							{% if i == page.current %}
							<b>{{ i }}</b>
							{% else %}
							<a href="/admin/coupon_users/create/coupon_id:{{ coupon.id }}/page:{{ i }}{% if keyword %}?keyword={{ keyword }}{% endif %}">{{ i }}</a>
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
<script>
	for (let checkboxes = document.querySelectorAll('input[type=checkbox]'), i = checkboxes.length; i--; ) {
		let checkbox = checkboxes[i];
		checkbox.onclick = () => {
			if (typeof checkbox.value === 'undefined') {
				return
			}
			document.getElementById('new_user').submit()
		}
	}
</script>
