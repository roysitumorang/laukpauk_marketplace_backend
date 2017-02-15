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
				<div class="tabs">
					{{ partial('partials/tabs_coupon', ['coupon': coupon, 'expand': 'users']) }}
					<div class="tab-content">
						<div id="areas" class="tab-pane active">
							{{ flashSession.output() }}
							<form method="GET" action="/admin/coupon_users/index/coupon_id:{{ coupon.id }}">
								<table class="table table-striped">
									<tr>
										<td>
											<select name="role_id">
												<option value="">Buyer &amp; Merchant</option>
												{% for role in roles %}
												<option value="{{ role.id }}"{% if role.id == role_id %} selected{% endif %}>Hanya {{ role.name }}</option>
												{% endfor %}
											</select>
											<input type="text" name="keyword" value="{{ keyword }}" maxlength="15" placeholder="Nama / Nomor HP">
											<button type="submit" class="btn btn-info">CARI</button>
										</td>
									</tr>
								</table>
							</form>
							<form method="POST" action="/admin/coupon_users/create/coupon_id:{{ coupon.id }}{% if current_page > 1 %}/page:{{ current_page }}{% endif %}{% if query_string %}?{{ query_string }}{% endif %}">
								<table class="table table-striped">
									<thead>
										<tr>
											<th width="25"><b>No</b></th>
											<th><b>Nama</b></th>
											<th><b>Role</b></th>
											<th><b>Nomor HP</b></th>
											<th><b>#</b></th>
										</tr>
									</thead>
									<tbody>
										{% for user in users %}
										<tr>
											<td>{{ user.rank }}</td>
											<td>{{ user.name }}</td>
											<td>{{ user.role }}</td>
											<td>{{ user.mobile_phone }}</td>
											<td><input type="checkbox" name="users[{{ user.id }}]" value="1"{% if user.coupon_id %} checked{% endif %}></i></td>
										</tr>
										{% elsefor %}
										<tr>
											<td colspan="5"><i>Belum ada member</i></td>
										</tr>
										{% endfor %}
										<tr>
											<td colspan="5" class="text-right"><button type="submit" class="btn btn-info">SIMPAN</button></td>
										</tr>
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
										<a href="/admin/coupon_users/index/coupon_id:{{ coupon.id }}/page:{{ i }}{% if keyword %}?keyword={{ keyword }}{% endif %}">{{ i }}</a>
										{% endif %}
									{% endfor %}
								</p>
							</div>
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
	for (var items = document.querySelectorAll('.fa-trash-o'), i = items.length; i--; ) {
		let item = items[i];
		item.onclick = () => {
			if (confirm('Anda yakin ingin menghapus member ini ?')) {
				fetch('/admin/coupon_users/delete/' + item.dataset.userId + '/coupon_id:' + item.dataset.couponId, { credentials: 'include', method: 'POST' }).then(() => {
					window.location.reload()
				})
			}
		}
	}
</script>
