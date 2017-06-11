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
						<li><span>Daftar Produk</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Daftar Produk</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				<div class="tabs">
					{{ partial('partials/tabs_group', ['expand': 'index']) }}
					<div class="tab-content">
						<div id="groups" class="tab-pane active">
							{{ flashSession.output() }}
							<table class="table table-striped">
								<tr>
									<td>
										<strong>Group Produk</strong>
										<select name="group_id" onchange="location.href='/admin/group_products/index/group_id:'+this.value">
										{% for item in groups %}
											<option value="{{ item.id }}"{% if item.id == group.id %} selected{% endif %}>{{ item.name }} ({{ item.total_products }})</option>
										{% endfor %}
										</select>
										<a type="button" href="/admin/group_products/create/group_id:{{ group.id }}" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Tambah Produk</a>
									</td>
								</tr>
							</table>
							{% if count(products) %}
								<div class="panel panel-default">
									<div class="panel-body">
										<form method="POST" action="/admin/group_products/truncate/group_id:{{ group.id }}" onsubmit="return confirm('Hapus semua produk dari group ?')">
											<strong>Total Produk : {{ number_format(group.total_products) }}</strong>
											<button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash-o"></i> Hapus semua produk</button>
										</form>
									</div>
								</div>
								<div class="row">
								{% for product in products %}
									<div class="col-md-3 panel">
										<div class="panel-body panel-featured text-center">
											<img src="/assets/image/{% if product.picture %}{{ product.thumbnails[0] }}{% else %}no_picture_120.png{% endif %}" border="0" width="150" height="150">
											<br>
											<strong>{{ product.name }}<br>({{ product.stock_unit }})</strong><br>
											<form method="POST" action="/admin/group_products/delete/group_id:{{ group.id }}/product_id:{{ product.id }}" onsubmit="return confirm('Hapus produk ini dari group ?')">
												Rp. {{ number_format(product.price) }}
												<button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash-o"></i></button>
												{% if !product.published %} <font color="#FF0000"><i class="fa fa-eye-slash"></i></font>{% endif %}
											</form>
										</div>
									</div>
								{% endfor %}
								</div>
							{% else %}
								<div class="panel panel-default">
									<div class="panel-body">Tidak ada produk dalam group</div>
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