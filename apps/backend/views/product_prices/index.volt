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
				<a href="/admin/users/update/{{ user.id }}"><h2>Update Member #{{ user.id }}</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span><a href="/admin/users">Daftar Member</a></span></li>
						<li><span><a href="/admin/users/update/{{ user.id }}">Update Member #{{ user.id }}</a></span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Update Member #{{ user.id }}</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				{{ flashSession.output() }}
				<div class="tabs">
					{{ partial('partials/tabs_user', ['user': user, 'expand': 'products']) }}
					<div class="tab-content">
						<div id="areas" class="tab-pane active">
							<p style="margin-left:5px"><i class="fa fa-plus-square"></i>&nbsp;<a href="/admin/product_prices/create/user_id:{{ user.id }}">Tambah Produk</a></p>
							<table class="table table-striped">
								<thead>
									<tr>
										<th width="5%"><b>No</b></th>
										<th><b>Kategori</b></th>
										<th><b>Produk</b></th>
										<th><b>Harga</b></th>
										<th><b>Jam Order Maksimal</b></th>
										<th><b>#</b></th>
									</tr>
								</thead>
								<tbody>
								{% for price in prices %}
									<tr>
										<td style="text-right">{{ price.rank }}</td>
										<td>{{ price.category }}</td>
										<td>{{ price.name }} ({{ price.stock_unit }})</td>
										<td>Rp. {{ number_format(price.value) }}</td>
										<td class="text-center">{{ price.order_closing_hour|default('-') }}</td>
										<td>
											<a href="javascript:void(0)" data-user-id="{{ user.id }}" data-id="{{ price.id }}" class="publish">{% if !price.published %}<font color="#FF0000">{% endif %}<i class="fa fa-eye fa-2x">{% if !price.published %}</font>{% endif %}</i></a>
											<a href="/admin/product_prices/update/{{ price.id }}/user_id:{{ user.id }}" title="Update"><i class="fa fa-pencil fa-2x"></i></a>
											<a href="javascript:void(0)" data-user-id="{{ user.id }}" data-id="{{ price.id }}" class="delete" title="Hapus"><i class="fa fa-trash-o fa-2x"></i></a>
										</td>
									</tr>
								{% elsefor %}
									<tr>
										<td colspan="6"><i>Belum ada produk</i></td>
									</tr>
								{% endfor %}
								</tbody>
							</table>
						</div>
					</div>
				</div>
				{% if page.total_pages > 1 %}
				<div class="weepaging">
					<p>
						<b>Halaman:</b>&nbsp;&nbsp;
						{% for i in pages %}
							{% if i == page.current %}
							<b>{{ i }}</b>
							{% else %}
							<a href="/admin/product_prices/index/user_id:{{ user.id }}/page:{{ i }}">{{ i }}</a>
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
	for (var items = document.querySelectorAll('.delete'), i = items.length; i--; ) {
		items[i].onclick = function() {
			if (!confirm('Anda yakin menghapus data ini ?')) {
				return !1
			}
			var form = document.createElement('form');
			form.method = 'POST',
			form.action = '/admin/product_prices/delete/' + this.dataset.id + '/user_id:' + this.dataset.userId,
			document.body.appendChild(form),
			form.submit()
		}
	}
	for (var items = document.querySelectorAll('.publish'), i = items.length; i--; ) {
		items[i].onclick = function() {
			var form = document.createElement('form');
			form.method = 'POST',
			form.action = '/admin/product_prices/update/' + this.dataset.id + '/user_id:' + this.dataset.userId + '/published:1',
			document.body.appendChild(form),
			form.submit()
		}
	}
</script>