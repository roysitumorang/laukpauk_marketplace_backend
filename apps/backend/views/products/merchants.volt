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
				<a href="/admin/products/merchants/{{ product.id }}{% if pagination.current > 1 %}/page:{{ pagination.current }}{% endif %}"><h2>Daftar Merchant</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><a href="/admin/products">Daftar Produk</a></li>
						<li><a href="/admin/products/{{ product.id }}">{{ product.name }} {{ product.stock_unit }}</a></li>
						<li><span>Daftar Merchant</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Daftar Merchant Produk {{ product.name }} {{ product.stock_unit }}</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				<div class="tabs">
					{{ partial('partials/tabs_product', ['expand': 'merchants']) }}
					<div class="tab-content">
						<div id="merchants" class="tab-pane active">
							{{ flashSession.output() }}
							<table class="table table-striped">
								<thead>
									<tr>
										<th width="25">No</th>
										<th>Merchant</th>
									</tr>
								</thead>
								<tbody>
								{% for user in users %}
									<tr>
										<td class="text-right">{{ user.rank }}</td>
										<td>{{ user.company }}</td>
									</tr>
								{% elsefor %}
									<tr>
										<td colspan="2"><i>Belum ada merchant</i></td>
									</tr>
								{% endfor %}
								</tbody>
							</table>
							{% if pagination.last > 1 %}
							<div class="weepaging">
								<p>
									<b>Halaman:</b>&nbsp;&nbsp;
									{% for i in pages %}
										{% if i == pagination.current %}
											<b>{{ i }}</b>
										{% else %}
											<a href="/admin/products/merchants/{{ product.id }}{% if i > 1 %}/page:{{ i }}{% endif %}">{{ i }}</a>
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