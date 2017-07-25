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
				<a href="/admin/banners{% if user_id %}/index/user_id:{{ user_id }}{% endif %}"><h2>Banner</h2></a>
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
				<a type="button" href="/admin/banners/create" class="btn btn-primary"><i class="fa fa-plus-square"></i>&nbsp;Tambah Banner</a>
				<br><br>
				<table class="table table-striped">
				{% for banner in banners %}
					<tr>
						<td>
							<a href="/assets/image/{{ banner.file }}" class="image-popup-no-margins"><img src="/assets/image/{{ banner.file }}" border="0" width="500px" height="250px"></a>
						</td>
						<td width="5%">
							<form method="POST" action="/admin/banners/{{ banner.id }}/{% if banner.published %}un{% endif %}publish">
								<button type="submit" class="btn btn-primary">
									<i class="fa fa-eye{% if !banner.published %}-slash{% endif %} fa-2x"></i>
								</button>
							</form>
							<br>
							<a type="button" href="/admin/banners/{{ banner.id }}/update" class="btn btn-primary" title="Ubah"><i class="fa fa-pencil-square fa-2x"></i></a>
							<br>
							<br>
							<form method="POST" action="/admin/banners/{{ banner.id }}/delete">
								<button type="submit" class="btn btn-primary">
									<i class="fa fa-trash-o fa-2x"></i>
								</button>
							</form>
						</td>
					</tr>
				{% endfor %}
				</table>
				{% if page.total_pages > 1 %}
				<div class="weepaging">
					<p>
						<b>Halaman:</b>&nbsp;&nbsp;
						{% for i in pages %}
							{% if i == page.current %}
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