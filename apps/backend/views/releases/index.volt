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
				<a href="/admin/releases{% if page.current > 1%}/index/page={{ page.current }}{% endif %}"><h2>Daftar Release APK</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span>Daftar Release APK</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Daftar Release APK</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				{{ flashSession.output() }}
				<p style="margin-left:5px"><a type="button" href="/admin/releases/create" class="btn btn-primary"><i class="fa fa-plus-square"></i> Tambah</a></p>
				<table class="table table-striped">
					<thead>
						<tr>
							<th class="text-center" width="5%">No</th>
							<th class="text-center">Versi</th>
							<th class="text-center">Tipe User</th>
							<th class="text-center">Fitur</th>
							<th class="text-center">Tanggal</th>
							<th class="text-center">#</th>
						</tr>
					</thead>
					<tbody>
					{% for release in releases %}
						<tr id="{{ release.id }}">
							<td class="text-right">{{ release.rank }}</td>
							<td>{{ release.version }}</td>
							<td>{{ release.user_type }}</td>
							<td>{{ release.features }}</td>
							<td>{{ release.created_at }}</td>
							<td class="text-center">
								<a href="/admin/releases/{{ release.id }}/update" title="Update"><i class="fa fa-pencil fa-2x"></i></a>
							</td>
						</tr>
					{% elsefor %}
						<tr>
							<td colspan="6"><i>Belum ada data</i></td>
						</tr>
					{% endfor %}
					</tbody>
				</table>
				{% if page.last > 1 %}
				<div class="weepaging">
					<p>
						<b>Halaman:</b>&nbsp;&nbsp;
						{% for i in pages %}
							{% if i == page.current %}
							<b>{{ i }}</b>
							{% else %}
							<a href="/admin/releases{% if i > 1 %}/index/page={{ i }}{% endif %}">{{ i }}</a>
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