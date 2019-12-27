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
				<a href="/admin/banners{% if user_id %}/index/user_id:{{ user_id }}{% endif %}{% if pagination.current > 1 %}/page:{{ pagination.current }}{% endif %}"><h2>Banner</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span>Banner</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Banner</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				{{ flashSession.output() }}
				{{ form('/admin/banners/create', 'enctype': 'multipart/form-data') }}
					<table class="table table-striped">
						<tr>
							<td>
								<b>Gambar :</b>
								<br>
								{{ fileField('new_file') }}
							</td>
							<td>
								<b>Status :</b>
								<br>
								{{ radio_field('published', 'value': 1, 'checked': banner.published ? true: null, 'id': 'published_' ~ 1) }} Tampilkan&nbsp;&nbsp;
								{{ radio_field('published', 'value': 0, 'checked': banner.published ? null: true, 'id': 'published_' ~ 1) }} Sembunyikan
							</td>
							<td>
								<button type="submit" class="btn btn-primary">UPLOAD</button>
							</td>
						</tr>
					</table>
				{{ endForm() }}
				<table class="table table-striped">
				{% for banner in banners %}
					<tr>
						<td>
							<a href="/assets/image/{{ banner.file }}" class="image-popup-no-margins"><img src="/assets/image/{{ banner.file }}" border="0" width="300px"></a>
						</td>
						<td>
							{{ form('/admin/banners/' ~ banner.id ~ '/toggle_status') }}
								<button type="submit" class="btn btn-primary">
									<i class="fa fa-eye{% if !banner.published %}-slash{% endif %} fa-2x"></i>
								</button>
							{{ endForm() }}
						</td>
						<td>
							{{ form('/admin/banners/' ~ banner.id ~ '/delete') }}
								<button type="submit" class="btn btn-danger" onclick="return confirm('Anda yakin mau menghapus banner ini ?')">
									<i class="fa fa-trash-o fa-2x"></i>
								</button>
							{{ endForm() }}
						</td>
					</tr>
				{% elsefor %}
					<tr>
						<td colspan="3">Belum ada data</td>
					</tr>
				{% endfor %}
				</table>
				{% if pagination.last > 1 %}
				<div class="weepaging">
					<p>
						<b>Halaman:</b>&nbsp;&nbsp;
						{% for i in pages %}
							{% if i == pagination.current %}
								<b>{{ i }}</b>
							{% else %}
								<a href="/admin/banners/index{% if user_id %}/user_id:{{ user_id }}{% endif %}{% if i > 1 %}/page:{{ i }}{% endif %}">{{ i }}</a>
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