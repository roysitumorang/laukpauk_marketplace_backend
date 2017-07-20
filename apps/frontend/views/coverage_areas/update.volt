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
				<a href="/coverage_areas/update{% if page.current > 1%}/page:{{ page.current }}{% endif %}"><h2>Update Area Operasional</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/"><i class="fa fa-home"></i></a></li>
						<li><span>Update Area Operasional</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Update Area Operasional</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				{{ flashSession.output() }}
				{% if coverage_areas %}
				<form method="POST" action="/coverage_areas/update{% if page.current > 1 %}/page:{{ page.current }}{% endif %}">
				{% endif %}
					<table class="table table-striped">
						<thead>
							<tr>
								<th width="5%"><b>No</b></th>
								<th><b>Propinsi</b></th>
								<th><b>Kabupaten / Kota</b></th>
								<th><b>Kecamatan</b></th>
								<th><b>Kelurahan</b></th>
								<th><b>Ongkos Kirim</b></th>
								<th><b>#</b></th>
							</tr>
						</thead>
						<tbody>
						{% for coverage_area in coverage_areas %}
							<tr>
								<td>
									{{ coverage_area.rank }}
									<input type="hidden" name="id[]" value="{{ coverage_area.id }}">
								</td>
								<td>{{ coverage_area.province_name }}</td>
								<td>{{ coverage_area.city_name }}</td>
								<td>{{ coverage_area.subdistrict_name }}</td>
								<td>{{ coverage_area.village_name }}</td>
								<td>
									<input type="text" name="shipping_cost[]" value="{{ coverage_area.shipping_cost }}" placeholder="Ongkos Kirim">
								</td>
								<td>
									<a href="javascript:void(0)" data-user-id="{{ user.id }}" data-id="{{ coverage_area.village_id }}" class="delete" title="Hapus"><i class="fa fa-trash-o fa-2x"></i></a>
								</td>
							</tr>
						{% elsefor %}
							<tr>
								<td colspan="7"><i>Belum ada area operasional</i></td>
							</tr>
						{% endfor %}
						{% if coverage_areas %}
							<tr>
								<td colspan="7" class="text-right">
									<button type="submit" class="btn btn-info">SIMPAN</button>
								</td>
							</tr>
						{% endif %}
						</tbody>
					</table>
				{% if coverage_areas %}
				</form>
				{% endif %}
				{% if page.total_pages > 1 %}
				<div class="weepaging">
					<p>
						<b>Halaman:</b>&nbsp;&nbsp;
						{% for i in pages %}
							{% if i == page.current %}
							<b>{{ i }}</b>
							{% else %}
							<a href="/coverage_areas/update{% if i > 1 %}/page:{{ i }}{% endif %}">{{ i }}</a>
							{% endif %}
						{% endfor %}
					</p>
				</div>
				{% endif %}
			</div>
		</section>
	{{ partial('partials/right_side') }}
</section>