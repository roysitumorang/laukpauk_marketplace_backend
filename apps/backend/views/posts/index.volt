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
				<a href="/admin/posts{% if user_id %}/index/user_id:{{ user_id }}{% endif %}{% if page.current > 1 %}/page:{{ page.current }}{% endif %}"><h2>Konten</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span>Konten</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Konten</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				{{ flashSession.output() }}
				<form method="POST" action="/admin/posts/save" enctype="multipart/form-data">
					<table class="table table-striped">
						<tr>
							<td>Merchant :</td>
							<td>
								<select name="user_id" onchange="location.href='/admin/posts'+(this.value?'/index/user_id:'+this.value:'')">
									<option value=""></option>
									{% for merchant in merchants %}
										<option value="{{ merchant.id }}"{% if merchant.id == user_id %} selected{% endif %}>{{ merchant.company }}</option>
									{% endfor %}
								</select>
							</td>
						</tr>
						{% for post in posts %}
						<tr>
							<td>{{ post.name }} :</td>
							<td><textarea name="posts[{{ post.post_category_id }}]" cols="70" rows="10" placeholder="{{ post.name }}">{{ post.body }}</textarea></td>
						</tr>
						{% endfor %}
					</table>
					<button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> SIMPAN</button>
				</form>
				{% if page.total_pages > 1 %}
				<div class="weepaging">
					<p>
						<b>Halaman:</b>&nbsp;&nbsp;
						{% for i in pages %}
							{% if i == page.current %}
							<b>{{ i }}</b>
							{% else %}
							<a href="/admin/posts/index{% if user_id %}/user_id:{{ user_id }}{% endif %}{% if i > 1 %}/page:{{ i }}{% endif %}">{{ i }}</a>
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