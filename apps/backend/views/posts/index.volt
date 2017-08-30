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
				<a href="/admin/posts{% if page.current > 1 %}/index/page:{{ page.current }}{% endif %}"><h2>Konten</h2></a>
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
				<form method="POST" action="/admin/posts" enctype="multipart/form-data">
					<table class="table table-striped">
						{% for post in posts %}
						<tr>
							<td>{{ post.subject }} :</td>
							<td><textarea name="body[{{ post.id }}]" cols="70" rows="10" placeholder="{{ post.subject }}">{{ post.body }}</textarea></td>
						</tr>
						{% endfor %}
					</table>
					<button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> SIMPAN</button>
				</form>
				<!-- eof Content //-->
			</div>
			<!-- end: page -->
		</section>
	</div>
	{{ partial('partials/right_side') }}
</section>