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
				<a href="/admin/banners{% if user_id %}/index/user_id:{{ user_id }}{% endif %}{% if page.current > 1 %}/page:{{ page.current }}{% endif %}"><h2>Banner</h2></a>
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
				<form method="POST" action="/admin/banners/create" enctype="multipart/form-data">
					<table class="table table-striped">
						<tr>
							<td>
								<b>Merchant :</b>
								<br>
								<select name="user_id" onchange="location.href='/admin/banners'+(this.value?'/index/user_id:'+this.value:'')">
									<option value=""></option>
									{% for merchant in merchants %}
										<option value="{{ merchant.id }}"{% if merchant.id == user_id %} selected{% endif %}>{{ merchant.company }} ({{ merchant.total_banners }})</option>
									{% endfor %}
								</select>
							</td>
							<td>
								<b>Gambar :</b>
								<br>
								<input type="file" name="new_file">
							</td>
							<td>
								<b>Status :</b>
								<br>
								<input type="radio" name="published" value="1"{% if banner.published %} checked{% endif %}> Tampilkan&nbsp;
								<input type="radio" name="published" value="0"{% if !banner.published %} checked{% endif %}> Sembunyikan
							</td>
							<td>
								<button type="submit" class="btn btn-primary">SIMPAN</button>
							</td>
						</tr>
					</table>
				</form>
				<table class="table table-striped">
				{% for banner in banners %}
					<tr>
						<td>
							<a href="/assets/image/{{ banner.file }}" class="image-popup-no-margins"><img src="/assets/image/{{ banner.file }}" border="0" width="300px"></a>
						</td>
						<td>
							<form method="POST" action="/admin/banners/{{ banner.id }}/{% if banner.published %}un{% endif %}publish">
								<button type="submit" class="btn btn-primary">
									<i class="fa fa-eye{% if !banner.published %}-slash{% endif %} fa-2x"></i>
								</button>
							</form>
						</td>
						<td>
							<form method="POST" action="/admin/banners/{{ banner.id }}/delete">
								<button type="submit" class="btn btn-danger">
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