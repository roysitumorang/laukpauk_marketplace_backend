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
				<a href="/admin/users/{{ user.id }}/sale_packages{% if page.current > 1%}/index/page:{{ page.current }}{% endif %}"><h2>Daftar Paket Belanja</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span><a href="/admin/users">Daftar Member</a></span></li>
						<li><span><a href="/admin/users/{{ user.id }}">{{ user.name }}</a></span></li>
						<li><span>Daftar Paket Belanja</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Daftar Paket Belanja {{ user.name }}</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				<div class="tabs">
					{{ partial('partials/tabs_user', ['user': user, 'expand': 'sale_packages']) }}
					<div class="tab-content">
						<div id="sale_packages" class="tab-pane active">
							{{ flashSession.output() }}
							<form method="GET" action="/admin/users/{{ user.id }}/sale_packages/index" id="search">
								<table class="table table-striped">
									<tr>
										<td>
											<input type="text" name="keyword" value="{{ keyword }}" placeholder="Cari">
											<button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Cari</button>
											<a type="button" href="/admin/users/{{ user.id }}/sale_packages/create" class="btn btn-primary"><i class="fa fa-plus-square"></i> Tambah</a>
										</td>
									</tr>
								</table>
							</form>
							<table class="table table-striped">
								<thead>
									<tr>
										<th class="text-center" width="5%">No</th>
										<th class="text-center">Nama</th>
										<th class="text-center">Harga</th>
										<th class="text-center"><b>#</b></th>
									</tr>
								</thead>
								<tbody>
								{% for sale_package in sale_packages %}
									<tr>
										<td class="text-right">{{ sale_package.rank }}</td>
										<td>{{ sale_package.name }}</td>
										<td>Rp. {{ sale_package.price | number_format(0, ',', '.') }}</td>
										<td class="text-center">
											<a href="javascript:void(0)" data-user-id="{{ sale_package.user_id }}" data-id="{{ sale_package.id }}" class="publish">
												<i class="fa fa-eye{% if !sale_package.published %}-slash{% endif %} fa-2x"></i>
											</a>
											<a href="/admin/users/{{ sale_package.user_id }}/sale_packages/{{ sale_package.id }}/update">
												<i class="fa fa-pencil fa-2x"></i>
											</a>
										</td>
									</tr>
								{% elsefor %}
									<tr>
										<td colspan="4"><i>Belum ada data</i></td>
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
											<a href="/admin/users/{{ user.id }}/sale_packages{% if i > 1 %}/index{% if keyword %}/keyword:{{ keyword }}{% endif %}/page:{{ i }}{% endif %}">{{ i }}</a>
										{% endif %}
									{% endfor %}
								</p>
							</div>
							{% endif %}
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
<script>
        let search = document.getElementById('search'), url = search.action, replacement = {' ': '+', ':': '', '\/': ''};
	document.querySelectorAll('.publish').forEach(item => {
		item.addEventListener('click', event => {
			let form = document.createElement('form');
			event.preventDefault(),
			form.method = 'POST',
			form.action = '/admin/users/' + item.dataset.userId + '/sale_packages/' + item.dataset.id + '/toggle_status?next={{ next }}',
			document.body.appendChild(form),
			form.submit()
		}, false)
	}),
	search.addEventListener('submit', event => {
		if (event.preventDefault(), search.keyword.value) {
			url += '/keyword:' + search.keyword.value.trim().replace(/ |:|\//g, match => {
				return replacement[match];
			})
		}
		location.href = url
	}, false)
</script>
