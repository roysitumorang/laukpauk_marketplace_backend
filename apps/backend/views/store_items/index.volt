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
				<a href="/admin/store_items/index/user_id:{{ user.id }}"><h2>Produk</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span><a href="/admin/users">Daftar Member</a></span></li>
						<li><span><a href="/admin/users/show/{{ user.id }}">{{ user.name }}</a></span></li>
						<li><span>Produk</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Produk {{ user.name }}</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				<div class="tabs">
					{{ partial('partials/tabs_user', ['user': user, 'expand': 'products']) }}
					<div class="tab-content">
						<div id="areas" class="tab-pane active">
							{{ flashSession.output() }}
							<p style="margin-left:5px"><i class="fa fa-plus-square"></i>&nbsp;<a href="javascript:void(0)" data-user-id="{{ user.id }}" class="new">Tambah Produk</a></p>
							<div id="form-wrapper"></div>
							<table class="table table-striped">
								<thead>
									<tr>
										<th class="text-center" width="5%"><b>No</b></th>
										<th class="text-center"><b>Kategori</b></th>
										<th class="text-center"><b>Produk</b></th>
										<th class="text-center"><b>Harga</b></th>
										<th class="text-center"><b>Stok</b></th>
										<th class="text-center"><b>Jam Order Maksimal</b></th>
										<th class="text-center"><b>#</b></th>
									</tr>
								</thead>
								<tbody>
								{% for store_item in store_items %}
									<tr>
										<td class="text-right">{{ store_item.rank }}</td>
										<td>{{ store_item.category }}</td>
										<td>{{ store_item.name }} ({{ store_item.stock_unit }})</td>
										<td>Rp. {{ number_format(store_item.price) }}</td>
										<td class="text-center">{{ store_item.stock }}</td>
										<td class="text-center">{{ store_item.order_closing_hour|default('-') }}</td>
										<td class="text-center">
											{% if store_item.price %}
											<a href="javascript:void(0)" data-user-id="{{ user.id }}" data-id="{{ store_item.id }}" class="publish">
											{% endif %}
											<i class="fa fa-eye{% if !store_item.published %}-slash{% endif %} fa-2x"></i>
											{% if store_item.price %}
											</a>
											{% endif %}
											<a href="javascript:void(0)" data-user-id="{{ user.id }}" data-id="{{ store_item.id }}" class="update" title="Update"><i class="fa fa-pencil fa-2x"></i></a>
											<a href="javascript:void(0)" data-user-id="{{ user.id }}" data-id="{{ store_item.id }}" class="delete" title="Hapus"><i class="fa fa-trash-o fa-2x"></i></a>
										</td>
									</tr>
								{% elsefor %}
									<tr>
										<td colspan="7"><i>Belum ada produk</i></td>
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
										<a href="/admin/store_items/index/user_id:{{ user.id }}/page:{{ i }}">{{ i }}</a>
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
	document.querySelector('.new').onclick = function() {
		fetch('/admin/store_items/create/user_id:' + this.dataset.userId, { credentials: 'include' }).then(response => {
			return response.text()
		}).then(data => {
			document.getElementById('form-wrapper').innerHTML = data
		})
	}
	for (var items = document.querySelectorAll('.update'), i = items.length; i--; ) {
		items[i].onclick = function() {
			fetch('/admin/store_items/update/' + this.dataset.id + '/user_id:' + this.dataset.userId, { credentials: 'include' }).then(response => {
				return response.text()
			}).then(data => {
				document.getElementById('form-wrapper').innerHTML = data
			})
		}
	}
	for (var items = document.querySelectorAll('.delete'), i = items.length; i--; ) {
		items[i].onclick = function() {
			if (!confirm('Anda yakin menghapus data ini ?')) {
				return !1
			}
			fetch('/admin/store_items/delete/' + this.dataset.id + '/user_id:' + this.dataset.userId, { credentials: 'include', method: 'POST' }).then(() => {
				window.location.reload()
			})
		}
	}
	for (var items = document.querySelectorAll('.publish'), i = items.length; i--; ) {
		items[i].onclick = function() {
			fetch('/admin/store_items/update/' + this.dataset.id + '/user_id:' + this.dataset.userId + '/published:1', { credentials: 'include', method: 'POST' }).then(response => {
				return response.json()
			}).then(payload => {
				payload.status===1 && window.location.reload()
			})
		}
	}
</script>