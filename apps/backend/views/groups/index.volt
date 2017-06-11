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
				<a href="/admin/groups"><h2>Group Produk</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span>Group Produk</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Group Produk</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				<div class="tabs">
					{{ partial('partials/tabs_group', ['expand': 'group']) }}
					<div class="tab-content">
						<div id="groups" class="tab-pane active">
							{{ flashSession.output() }}
							<table class="table table-striped">
								<tr>
									<td>
										<form method="GET" action="/admin/groups" id="search">
											<input type="text" name="keyword" value="{{ keyword }}" size="40" placeholder="Nama">&nbsp;
											<button type="submit" class="btn btn-primary">CARI</button>
										</form>
									</td>
								</tr>
							</table>
							<p style="margin-left:5px"><i class="fa fa-plus-square"></i>&nbsp;<a href="/admin/groups/create">Tambah Group Produk</a></p>
							<table class="table table-striped">
								<thead>
									<tr>
										<th width="1%">No</th>
										<th>Nama</th>
										<th>Jumlah Produk</th>
										<th>#</th>
									</tr>
								</thead>
								<tbody>
								{% for group in groups %}
									<tr id="{{ group.id }}">
										<td>{{ group.rank }}</td>
										<td>{{ group.name }}</td>
										<td>{{ group.total_products }}</td>
										<td>
											<a href="/admin/group_products/index/group_id:{{ group.id }}"><i class="fa fa-info-circle fa-2x"></i></a>
											<a href="javascript:void(0)" class="published" data-id="{{ group.id }}" data-published="{{ group.published }}">
												<i class="fa fa-eye{% if !group.published %}-slash{% endif %} fa-2x"></i>
											</a>
											<a href="/admin/groups/update/{{ group.id }}" title="Ubah"><i class="fa fa-pencil-square fa-2x"></i></a>
											{% if !group.total_products %}
											<a href="javascript:void(0)" class="delete" data-id="{{ group.id }}" title="Hapus"><i class="fa fa-trash-o fa-2x"></i></a>
											{% endif %}
										</td>
									</tr>
								{% elsefor %}
									<tr>
										<td colspan="4"><i>Belum ada group produk</i></td>
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
										<a href="/admin/groups/index{% if keyword %}/keyword:{{ keyword }}{% endif %}{% if i > 1 %}/page:{{ i }}{% endif %}">{{ i }}</a>
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
	let items = document.querySelectorAll('.published,.delete'), i = items.length, search = document.getElementById('search'), url = '/admin/groups/index', replacement = {' ': '+', ':': '', '\/': ''};
	for ( ; i--; ) {
		let item = items[i];
		items[i].onclick = () => {
			if ('delete' === item.className && !confirm('Anda yakin ingin menghapus group produk ini ?')) {
				return !1
			}
			let form = document.createElement('form');
			form.method = 'POST',
			form.action = 'delete' === item.className
			? '/admin/groups/delete/' + item.dataset.id
			: '/admin/groups/' + (item.dataset.published == 1 ? 'un' : '') + 'publish/' + item.dataset.id + '?next=' + window.location.href.split('#')[0] + '#' + item.dataset.id,
			document.body.appendChild(form),
			form.submit()
		}
	}
	search.addEventListener('submit', event => {
		event.preventDefault();
		if (search.keyword.value) {
			url += '/keyword:' + search.keyword.value.trim().replace(/ |:|\//g, match => {
				return replacement[match];
			});
		}
		location.href = url;
	}, false)
</script>
